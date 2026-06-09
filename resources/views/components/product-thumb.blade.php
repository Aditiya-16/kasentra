@props([
    'id' => 0,
    'name' => '',
    'image' => null,
    'category' => null,
    'rounded' => 'rounded-xl',
])

@php
    use Illuminate\Support\Facades\Storage;

    // Palet gradien khas — selaras dengan placeholder foto di aplikasi mobile.
    $palettes = [
        ['#0F766E', '#14B8A6'], // teal
        ['#4F46E5', '#818CF8'], // indigo
        ['#D97706', '#FBBF24'], // amber
        ['#BE185D', '#F472B6'], // pink
        ['#0369A1', '#38BDF8'], // sky
        ['#15803D', '#4ADE80'], // green
        ['#7C3AED', '#A78BFA'], // violet
        ['#B45309', '#F59E0B'], // orange
    ];
    [$c1, $c2] = $palettes[abs((int) $id) % count($palettes)];

    // Pilih ikon dari kata kunci kategori/nama (heroicons — gaya garis, putih).
    $text = strtolower(trim(($category ?? '') . ' ' . $name));
    $has = fn (array $keys) => collect($keys)->contains(fn ($k) => str_contains($text, $k));
    $icon = match (true) {
        $has(['kopi', 'coffee', 'teh', 'tea', 'minum', 'drink', 'jus', 'juice', 'soda', 'susu', 'milk']) => 'heroicon-o-beaker',
        $has(['roti', 'bread', 'kue', 'cake', 'donat', 'pastry', 'dessert', 'snack', 'cemilan', 'keripik', 'biskuit', 'cookie', 'chips']) => 'heroicon-o-cake',
        $has(['nasi', 'makan', 'food', 'ayam', 'mie', 'bakso', 'soto', 'sate']) => 'heroicon-o-fire',
        $has(['buah', 'fruit', 'sayur', 'veg', 'organik']) => 'heroicon-o-sparkles',
        $has(['elektronik', 'gadget', 'phone', 'electronic', 'hp', 'charger']) => 'heroicon-o-device-phone-mobile',
        $has(['baju', 'fashion', 'kaos', 'pakaian', 'cloth', 'celana']) => 'heroicon-o-shopping-bag',
        $has(['obat', 'medic', 'health', 'kesehatan', 'vitamin']) => 'heroicon-o-heart',
        default => 'heroicon-o-archive-box',
    };

    // Monogram (inisial) dari nama produk.
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $parts = array_values(array_filter($parts));
    $mono = match (count($parts)) {
        0 => '?',
        1 => mb_strtoupper(mb_substr($parts[0], 0, 1)),
        default => mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1)),
    };

    $url = $image ? Storage::disk('public')->url($image) : null;
@endphp

@if ($url)
    <img src="{{ $url }}" alt="{{ $name }}"
        {{ $attributes->merge(['class' => "aspect-square w-full $rounded object-cover"]) }}>
@else
    {{-- Foto contoh: gradien + ikon kategori + monogram (no network). --}}
    <div
        {{ $attributes->merge(['class' => "relative aspect-square w-full overflow-hidden $rounded"]) }}
        style="background-image: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }});"
        role="img" aria-label="{{ $name }}"
    >
        <div class="absolute -right-4 -top-4 h-14 w-14 rounded-full bg-white/15"></div>
        <div class="absolute -bottom-3 -left-2 h-10 w-10 rounded-full bg-white/10"></div>
        <div class="absolute inset-0 grid place-items-center">
            <x-dynamic-component :component="$icon" class="h-9 w-9 text-white/90" />
        </div>
        <span class="absolute left-1.5 top-1.5 rounded-md bg-white/25 px-1.5 py-0.5 text-[10px] font-extrabold tracking-wide text-white">
            {{ $mono }}
        </span>
    </div>
@endif
