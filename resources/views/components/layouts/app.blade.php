<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Kasir' }} · Kasentra</title>

    {{-- Favicon Kasentra --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">

    <script>
        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    {{-- Bundle RINGAN khusus aplikasi (tanpa GSAP/Three.js). Lottie sukses
         di-lazy import dari app.js hanya saat dibutuhkan. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 dark:bg-ink-950">
    {{ $slot }}
    @livewireScripts
</body>
</html>
