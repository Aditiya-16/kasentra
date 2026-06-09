<?php

namespace App\Livewire;

use App\Actions\CreateTransaction;
use App\Enums\PaymentMethod;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Kasir')]
class Cashier extends Component
{
    /** Kata kunci pencarian produk (nama atau SKU/barcode). */
    public string $search = '';

    /**
     * Keranjang, dipetakan: product_id => [id, name, price, qty, stock].
     * 'price' & 'stock' hanya untuk tampilan; harga final divalidasi ulang
     * dari DB saat checkout (lihat CreateTransaction).
     *
     * @var array<int, array{id:int,name:string,price:float,qty:int,stock:int,image:?string,category:?string}>
     */
    public array $cart = [];

    /** Uang dibayar pelanggan (string agar input kosong tidak jadi 0). */
    #[Validate('nullable|numeric|min:0')]
    public string $paid = '';

    #[Validate('required')]
    public string $paymentMethod = 'tunai';

    /** State layar sukses + ringkasan transaksi terakhir untuk ditampilkan. */
    public bool $showSuccess = false;

    public ?array $lastTransaction = null;

    /**
     * Hasil pencarian produk. #[Computed] = dihitung saat dibutuhkan dan
     * di-cache per request, jadi ringan. Hanya produk aktif yang tampil.
     */
    #[Computed]
    public function products(): Collection
    {
        $query = Product::query()
            ->where('is_active', true)
            ->select(['id', 'category_id', 'name', 'sku', 'price', 'stock', 'image'])
            ->with('category:id,name')
            ->orderBy('name')
            ->limit(24);

        $term = trim($this->search);
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        return $query->get();
    }

    /** Total belanja (qty × harga) — terhitung otomatis dari keranjang. */
    #[Computed]
    public function total(): float
    {
        return collect($this->cart)->sum(fn ($i) => $i['price'] * $i['qty']);
    }

    /** Kembalian = uang dibayar − total. Bisa negatif (uang belum cukup). */
    #[Computed]
    public function change(): float
    {
        return (float) ($this->paid === '' ? 0 : $this->paid) - $this->total();
    }

    /** URL gambar QRIS toko (dari Pengaturan). Null bila admin belum mengunggah. */
    #[Computed]
    public function qrisImageUrl(): ?string
    {
        $path = Setting::get('qris_image');

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /** Tambah produk ke keranjang (atau +1 qty bila sudah ada). */
    public function addToCart(int $productId): void
    {
        $product = Product::where('is_active', true)->find($productId);
        if (! $product || $product->stock < 1) {
            return;
        }

        $current = $this->cart[$productId]['qty'] ?? 0;
        // Jangan melebihi stok yang tersedia.
        if ($current + 1 > $product->stock) {
            return;
        }

        $this->cart[$productId] = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'qty' => $current + 1,
            'stock' => $product->stock,
            // Hanya untuk tampilan thumbnail di keranjang.
            'image' => $product->image,
            'category' => $product->category?->name,
        ];
    }

    public function incrementQty(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }
        $line = $this->cart[$productId];
        if ($line['stock'] >= $line['qty'] + 1) {
            $this->cart[$productId]['qty']++;
        }
    }

    public function decrementQty(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }
        if (--$this->cart[$productId]['qty'] < 1) {
            unset($this->cart[$productId]);
        }
    }

    public function removeItem(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->paid = '';
    }

    /** Tombol cepat: set uang dibayar = total (uang pas). */
    public function exactCash(): void
    {
        $this->paid = (string) $this->total();
    }

    /** Tombol cepat nominal uang (mis. 50.000, 100.000). */
    public function quickCash(int $amount): void
    {
        $this->paid = (string) ((float) ($this->paid === '' ? 0 : $this->paid) + $amount);
    }

    /** Selesaikan transaksi (PRD alur bagian 5). */
    public function checkout(CreateTransaction $createTransaction): void
    {
        $this->validate();

        $method = PaymentMethod::from($this->paymentMethod);

        // Untuk pembayaran non-tunai (QRIS/Transfer), nominal selalu pas =
        // total, jadi uang dibayar diset otomatis (tidak ada kembalian).
        if ($method !== PaymentMethod::Tunai) {
            $this->paid = (string) $this->total();
        }

        // Siapkan payload ringkas untuk action (id + qty saja).
        $payload = collect($this->cart)
            ->map(fn ($i) => ['id' => $i['id'], 'qty' => $i['qty']])
            ->values()
            ->all();

        // Bila gagal (stok kurang / uang kurang), ValidationException otomatis
        // ditangani Livewire dan ditampilkan ke user.
        $transaction = $createTransaction->handle(
            cashier: auth()->user(),
            cart: $payload,
            paid: (float) ($this->paid === '' ? 0 : $this->paid),
            method: $method,
        );

        // Simpan ringkasan untuk layar sukses & struk (Tahap 5).
        $this->lastTransaction = [
            'id' => $transaction->id,
            'invoice' => $transaction->invoice_number,
            'total' => (float) $transaction->total,
            'paid' => (float) $transaction->paid,
            'change' => (float) $transaction->change,
        ];

        // Reset keranjang untuk transaksi berikutnya.
        $this->reset(['cart', 'paid', 'search', 'paymentMethod']);
        $this->paymentMethod = 'tunai';
        unset($this->total, $this->change, $this->products);

        $this->showSuccess = true;

        // Picu animasi Lottie sukses (ditangani app.js).
        $this->dispatch('transaction-success');
    }

    /** Tutup layar sukses & siap transaksi baru. */
    public function newTransaction(): void
    {
        $this->showSuccess = false;
        $this->lastTransaction = null;
    }

    public function render()
    {
        return view('livewire.cashier');
    }
}
