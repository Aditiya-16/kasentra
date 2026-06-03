<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

echo 'path: ' . Setting::get('qris_image') . PHP_EOL;
echo 'url : ' . Storage::disk('public')->url(Setting::get('qris_image')) . PHP_EOL;
