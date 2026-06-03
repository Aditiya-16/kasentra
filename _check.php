<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo 'admin exists: ' . (User::where('email', 'admin@kasentra.test')->exists() ? 'yes' : 'no') . PHP_EOL;
echo 'admin creds valid: ' . (Auth::validate(['email' => 'admin@kasentra.test', 'password' => 'password']) ? 'YES' : 'NO') . PHP_EOL;
echo 'kasir creds valid: ' . (Auth::validate(['email' => 'kasir@kasentra.test', 'password' => 'password']) ? 'YES' : 'NO') . PHP_EOL;
echo 'wrong pass rejected: ' . (Auth::validate(['email' => 'admin@kasentra.test', 'password' => 'salah']) ? 'NO (bad)' : 'YES') . PHP_EOL;
