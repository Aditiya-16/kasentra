<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /** URL gambar QRIS toko (untuk layar pembayaran QRIS di kasir). */
    public function qris(): JsonResponse
    {
        $path = Setting::get('qris_image');

        return response()->json([
            'qris_image_url' => $path ? Storage::disk('public')->url($path) : null,
        ]);
    }

    /** Seluruh pengaturan toko (admin). */
    public function index(): JsonResponse
    {
        return response()->json($this->payload());
    }

    /** Simpan pengaturan toko (admin): nama toko + gambar QRIS opsional. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_name' => ['nullable', 'string', 'max:255'],
            'qris_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'remove_qris' => ['boolean'],
        ]);

        if (array_key_exists('store_name', $data)) {
            Setting::set('store_name', $data['store_name'] ?? 'Kasentra');
        }

        if ($request->hasFile('qris_image')) {
            $old = Setting::get('qris_image');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('qris_image')->store('settings', 'public');
            Setting::set('qris_image', $path);
        } elseif ($request->boolean('remove_qris')) {
            $old = Setting::get('qris_image');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('qris_image', null);
        }

        return response()->json($this->payload());
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        $qris = Setting::get('qris_image');

        return [
            'store_name' => Setting::get('store_name', 'Kasentra'),
            'qris_image_url' => $qris ? Storage::disk('public')->url($qris) : null,
        ];
    }
}
