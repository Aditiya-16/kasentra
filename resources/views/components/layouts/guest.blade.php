<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Masuk' }} · Kasentra</title>

    {{-- Favicon Kasentra --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">

    {{-- Cegah flash mode terang sebelum CSS dimuat. --}}
    <script>
        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    {{-- Bundle RINGAN: hanya app.css + app.js (tanpa GSAP/Three.js landing). --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen">
    {{ $slot }}
    @livewireScripts
</body>
</html>
