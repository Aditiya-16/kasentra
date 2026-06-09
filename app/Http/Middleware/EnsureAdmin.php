<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi endpoint pengelolaan (produk, kategori, pengguna, pengaturan)
 * hanya untuk admin. Kasir akan menerima 403.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $request->user() && $request->user()->isAdmin(),
            403,
            'Khusus admin.',
        );

        return $next($request);
    }
}
