<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Setelan aplikasi key-value (mis. 'qris_image', 'store_name').
 *
 * Nilai di-cache agar pembacaan (yang sering terjadi di halaman kasir)
 * tidak memukul database tiap kali.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    /** Ambil nilai setelan (dengan cache). */
    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever(
            "setting.{$key}",
            fn () => static::query()->find($key)?->value ?? $default,
        );
    }

    /** Simpan/ubah nilai setelan dan segarkan cache-nya. */
    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.{$key}");
    }
}
