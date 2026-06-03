<?php

namespace App\Livewire\Auth;

use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Proses login. Mengikuti PRD 4.1: login email+password, role-based.
     * Dilengkapi rate limiting (best practice keamanan: cegah brute force).
     */
    public function authenticate()
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        // Coba autentikasi pada guard 'web' (guard yang sama dipakai Filament,
        // sehingga setelah login admin bisa langsung membuka /admin).
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        // Tolak akun yang dinonaktifkan admin (PRD 4.1).
        if (! Auth::user()->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Akun Anda dinonaktifkan. Hubungi admin.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        session()->regenerate(); // cegah session fixation

        // Arahkan sesuai peran: Admin -> panel Filament, Kasir -> halaman kasir.
        return redirect()->intended(
            Auth::user()->role === UserRole::Admin ? '/admin' : route('kasir')
        );
    }

    /** Blokir sementara setelah 5 percobaan gagal dari email+IP yang sama. */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    protected function throttleKey(): string
    {
        return strtolower($this->email) . '|' . request()->ip();
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
