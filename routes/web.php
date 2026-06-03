<?php

use App\Http\Controllers\ReceiptController;
use App\Livewire\Auth\Login;
use App\Livewire\Cashier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Landing page publik Kasentra (Tahap 2).
Route::view('/', 'landing')->name('home');

// Halaman login elegan (Livewire). Hanya untuk tamu (belum login).
Route::get('/login', Login::class)->middleware('guest')->name('login');

// Logout — POST agar tidak bisa dipicu lewat link/CSRF.
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');

// Halaman kasir (Livewire) — fitur inti Kasentra (PRD 4.4). Untuk semua
// pengguna terautentikasi: kasir maupun admin (admin juga bisa bertransaksi).
Route::get('/kasir', Cashier::class)
    ->middleware('auth')
    ->name('kasir');

// Struk PDF (PRD 4.7). Otorisasi dilakukan di dalam controller.
Route::get('/struk/{transaction}', [ReceiptController::class, 'show'])
    ->middleware('auth')
    ->name('receipt');
