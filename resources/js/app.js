/*
 | Bundle global Kasentra (dimuat di semua halaman, termasuk halaman kasir).
 |
 | PENTING: jaga bundle ini tetap RINGAN. Jangan import GSAP / Three.js / Spline
 | di sini, karena akan ikut terunduh di halaman kasir yang wajib cepat.
 | - Animasi berat (GSAP scroll, 3D) -> resources/js/landing.js (hanya landing).
 |
 | Alpine.js TIDAK perlu di-import manual: Livewire 3 sudah membawanya.
 */

/*
 | LOTTIE SUKSES TRANSAKSI
 | Komponen kasir mengirim event 'transaction-success' (via Livewire dispatch)
 | saat transaksi tuntas. Kita lazy-import lottie-web HANYA di sini, sehingga
 | library-nya menjadi chunk terpisah yang baru diunduh ketika benar-benar
 | dibutuhkan — bukan saat halaman kasir pertama kali dibuka.
 */
let successAnim = null;

window.addEventListener('transaction-success', async () => {
    const container = document.getElementById('success-lottie');
    if (!container) return;

    // Bersihkan animasi sebelumnya bila ada (transaksi beruntun).
    if (successAnim) successAnim.destroy();

    try {
        const lottie = (await import('lottie-web')).default;
        successAnim = lottie.loadAnimation({
            container,
            renderer: 'svg',
            loop: false,
            autoplay: true,
            path: '/lottie/success.json', // file statis di public/, tidak di-bundle
        });
    } catch (e) {
        // Jika gagal memuat, fallback CSS (lihat .check-fallback) tetap tampil.
        console.warn('Lottie gagal dimuat, memakai fallback.', e);
    }
});
