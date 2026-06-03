/*
 | Bundle khusus LANDING PAGE Kasentra — boleh "mewah".
 | Berisi: (1) scene 3D Three.js, (2) scroll-reveal GSAP, (3) toggle dark mode.
 | HANYA dimuat di landing (lihat vite.config.js), tidak membebani halaman kasir.
 */

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import * as THREE from 'three';

gsap.registerPlugin(ScrollTrigger);

/* =====================================================================
 | 1. TOGGLE DARK MODE
 | Menyimpan preferensi di localStorage. Kelas .dark sudah dipasang lebih
 | awal oleh inline script di <head> untuk mencegah flicker.
 ===================================================================== */
function initThemeToggle() {
    const btn = document.getElementById('theme-toggle');
    if (!btn) return;
    btn.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.theme = isDark ? 'dark' : 'light';
    });
}

/* =====================================================================
 | 2. SCENE 3D — "toko/kasir" melayang & berputar pelan
 | Stilasi low-poly: badan toko (kubus rounded) + atap + awning bergaris,
 | dengan koin yang mengorbit. Warna mengikuti palet brand (teal/indigo).
 ===================================================================== */
function initHeroScene() {
    const canvas = document.getElementById('hero-3d');
    if (!canvas) return;

    // Hormati preferensi "kurangi animasi" demi aksesibilitas.
    const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

    const scene = new THREE.Scene();
    const sizes = { w: canvas.clientWidth, h: canvas.clientHeight };

    const camera = new THREE.PerspectiveCamera(40, sizes.w / sizes.h, 0.1, 100);
    camera.position.set(0, 1.2, 7);

    const renderer = new THREE.WebGLRenderer({
        canvas,
        antialias: true,
        alpha: true, // transparan agar menyatu dengan gradien latar
    });
    renderer.setSize(sizes.w, sizes.h);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    // --- Pencahayaan (soft) ---
    scene.add(new THREE.AmbientLight(0xffffff, 0.7));
    const key = new THREE.DirectionalLight(0xffffff, 1.4);
    key.position.set(3, 5, 4);
    scene.add(key);
    const rim = new THREE.PointLight(0x6366f1, 40, 20); // indigo rim light
    rim.position.set(-4, 2, -2);
    scene.add(rim);

    // --- Grup induk: semua objek toko, supaya bisa diputar bersama ---
    const shop = new THREE.Group();
    scene.add(shop);

    const teal = new THREE.MeshStandardMaterial({ color: 0x14b8a6, roughness: 0.35, metalness: 0.1 });
    const tealDark = new THREE.MeshStandardMaterial({ color: 0x0f766e, roughness: 0.4 });
    const cream = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.6 });
    const amber = new THREE.MeshStandardMaterial({ color: 0xf59e0b, roughness: 0.3, metalness: 0.2 });

    // Badan toko
    const body = new THREE.Mesh(new THREE.BoxGeometry(2.4, 1.6, 2.0), cream);
    body.position.y = -0.2;
    shop.add(body);

    // Atap (prisma segitiga via cone 4 sisi)
    const roof = new THREE.Mesh(new THREE.ConeGeometry(1.9, 1.0, 4), tealDark);
    roof.position.y = 1.1;
    roof.rotation.y = Math.PI / 4;
    shop.add(roof);

    // Awning bergaris di depan
    const awning = new THREE.Mesh(new THREE.BoxGeometry(2.5, 0.18, 0.6), teal);
    awning.position.set(0, 0.35, 1.05);
    shop.add(awning);

    // Pintu
    const door = new THREE.Mesh(new THREE.BoxGeometry(0.6, 1.0, 0.05), tealDark);
    door.position.set(0, -0.5, 1.01);
    shop.add(door);

    // Koin-koin yang mengorbit (representasi penjualan/omzet)
    const coins = [];
    for (let i = 0; i < 3; i++) {
        const coin = new THREE.Mesh(new THREE.CylinderGeometry(0.28, 0.28, 0.06, 32), amber);
        coin.rotation.x = Math.PI / 2;
        coins.push(coin);
        scene.add(coin);
    }

    // Animasi masuk: toko "muncul" dengan skala (kalau motion diizinkan)
    if (!reduceMotion) {
        shop.scale.setScalar(0.2);
        gsap.to(shop.scale, { x: 1, y: 1, z: 1, duration: 1.1, ease: 'back.out(1.7)' });
    }

    // --- Loop render ---
    const clock = new THREE.Clock();
    function tick() {
        const t = clock.getElapsedTime();

        if (!reduceMotion) {
            shop.rotation.y = t * 0.35;          // berputar pelan
            shop.position.y = Math.sin(t * 1.2) * 0.12; // melayang naik-turun
            coins.forEach((coin, i) => {
                const a = t * 0.8 + (i * (Math.PI * 2)) / 3;
                coin.position.set(Math.cos(a) * 2.6, 0.4 + Math.sin(t * 1.5 + i) * 0.2, Math.sin(a) * 2.6);
                coin.rotation.z = t * 2 + i;
            });
        }

        renderer.render(scene, camera);
        requestAnimationFrame(tick);
    }
    tick();

    // Responsif terhadap perubahan ukuran kanvas
    window.addEventListener('resize', () => {
        sizes.w = canvas.clientWidth;
        sizes.h = canvas.clientHeight;
        camera.aspect = sizes.w / sizes.h;
        camera.updateProjectionMatrix();
        renderer.setSize(sizes.w, sizes.h);
    });
}

/* =====================================================================
 | 3. SCROLL-REVEAL dengan GSAP + ScrollTrigger
 | Setiap elemen [data-reveal] memudar & naik halus saat masuk layar.
 ===================================================================== */
function initScrollReveal() {
    const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const items = gsap.utils.toArray('[data-reveal]');

    if (reduceMotion) {
        // Tanpa animasi: pastikan semua tetap terlihat.
        gsap.set(items, { opacity: 1, y: 0 });
        return;
    }

    items.forEach((el) => {
        gsap.fromTo(
            el,
            { opacity: 0, y: 40 },
            {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 85%' },
            }
        );
    });
}

// Jalankan setelah DOM siap.
document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initHeroScene();
    initScrollReveal();
});
