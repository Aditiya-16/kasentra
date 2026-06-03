<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Railway/proxy menutup TLS di edge lalu meneruskan request sebagai HTTP.
        // Percayai header X-Forwarded-* agar Laravel tahu koneksi aslinya HTTPS,
        // sehingga semua URL & form (mis. tombol Logout) memakai skema https.
        $middleware->trustProxies(at: '*');

        // Ke mana user yang SUDAH login diarahkan bila membuka halaman "tamu"
        // seperti /login. Sesuai peran: admin -> panel, kasir -> halaman kasir.
        $middleware->redirectUsersTo(fn (Request $request) => $request->user()?->isAdmin()
            ? '/admin'
            : route('kasir'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
