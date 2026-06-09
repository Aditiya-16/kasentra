<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /** Daftar pengguna (admin). */
    public function index(): JsonResponse
    {
        $users = User::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => $this->payload($u));

        return response()->json(['data' => $users]);
    }

    /** Tambah pengguna baru (admin). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['boolean'],
        ]);

        $user = User::create($data);

        return response()->json(['data' => $this->payload($user)], 201);
    }

    /** Ubah pengguna (admin). Password opsional saat edit. */
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['boolean'],
        ]);

        // Cegah admin mengunci dirinya sendiri (turun peran / nonaktif).
        if ($user->id === $request->user()->id) {
            if (($data['role'] ?? $user->role->value) !== UserRole::Admin->value) {
                return $this->guard('Anda tidak bisa menurunkan peran akun sendiri.');
            }
            if (array_key_exists('is_active', $data) && ! $data['is_active']) {
                return $this->guard('Anda tidak bisa menonaktifkan akun sendiri.');
            }
        }

        // Jangan sampai admin terakhir hilang (demosi/nonaktif).
        if ($this->wouldRemoveLastAdmin($user, $data)) {
            return $this->guard('Minimal harus ada satu admin aktif.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json(['data' => $this->payload($user->fresh())]);
    }

    /** Hapus pengguna (admin). Tidak boleh menghapus diri sendiri/admin terakhir. */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return $this->guard('Anda tidak bisa menghapus akun sendiri.');
        }

        if ($user->isAdmin() && $this->activeAdminCount() <= 1) {
            return $this->guard('Minimal harus ada satu admin aktif.');
        }

        $user->delete();

        return response()->json(['message' => 'Pengguna dihapus.']);
    }

    /**
     * Apakah perubahan ini akan menghilangkan admin aktif terakhir?
     *
     * @param  array<string, mixed>  $data
     */
    private function wouldRemoveLastAdmin(User $user, array $data): bool
    {
        if (! $user->isAdmin() || ! $user->is_active) {
            return false;
        }

        $stillAdmin = ($data['role'] ?? UserRole::Admin->value) === UserRole::Admin->value;
        $stillActive = $data['is_active'] ?? true;

        return (! $stillAdmin || ! $stillActive) && $this->activeAdminCount() <= 1;
    }

    private function activeAdminCount(): int
    {
        return User::where('role', UserRole::Admin)->where('is_active', true)->count();
    }

    private function guard(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 422);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'role_label' => $user->role->getLabel(),
            'is_admin' => $user->isAdmin(),
            'is_active' => $user->is_active,
        ];
    }
}
