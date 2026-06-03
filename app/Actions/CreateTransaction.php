<?php

namespace App\Actions;

use App\Enums\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Memproses satu transaksi penjualan (PRD alur bagian 5 & fitur 4.4).
 *
 * Dipisah dari komponen Livewire agar:
 *  - logika inti mudah diuji secara terpisah (unit/feature test),
 *  - tidak ada duplikasi bila nanti dipakai dari tempat lain (mis. API).
 *
 * Seluruh proses dibungkus DB::transaction + lockForUpdate sehingga aman
 * dari race condition (mencegah stok terjual melebihi yang tersedia bila
 * dua kasir checkout bersamaan).
 */
class CreateTransaction
{
    /**
     * @param  array<int, array{id: int, qty: int}>  $cart  daftar item (id produk + qty)
     *
     * @throws ValidationException bila keranjang kosong, stok kurang, atau uang dibayar < total
     */
    public function handle(User $cashier, array $cart, float $paid, PaymentMethod $method): Transaction
    {
        if (empty($cart)) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang masih kosong.',
            ]);
        }

        return DB::transaction(function () use ($cashier, $cart, $paid, $method) {
            // Kunci baris produk agar stok tidak berubah selama proses.
            $ids = array_column($cart, 'id');
            $products = Product::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            $total = 0;
            $itemsData = [];

            foreach ($cart as $line) {
                $product = $products->get($line['id']);
                $qty = (int) $line['qty'];

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Ada produk yang tidak tersedia. Muat ulang halaman.',
                    ]);
                }

                if ($qty < 1 || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$product->name} tidak mencukupi (tersisa {$product->stock}).",
                    ]);
                }

                // Harga & nama diambil dari DB saat ini lalu DISIMPAN sebagai
                // snapshot — riwayat tetap akurat meski produk diubah nanti.
                $subtotal = (float) $product->price * $qty;
                $total += $subtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            // Validasi uang dibayar (kembalian tidak boleh negatif).
            if ($paid < $total) {
                throw ValidationException::withMessages([
                    'paid' => 'Uang dibayar kurang dari total belanja.',
                ]);
            }

            // Buat transaksi. invoice_number sementara unik, lalu diformat ulang
            // memakai ID (dijamin unik) menjadi INV-YYYYMMDD-00001.
            $transaction = Transaction::create([
                'invoice_number' => 'TMP-' . uniqid(),
                'user_id' => $cashier->id,
                'total' => $total,
                'paid' => $paid,
                'change' => $paid - $total,
                'payment_method' => $method,
            ]);

            $transaction->update([
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-'
                    . str_pad((string) $transaction->id, 5, '0', STR_PAD_LEFT),
            ]);

            $transaction->items()->createMany($itemsData);

            // Kurangi stok masing-masing produk (PRD: stok berkurang otomatis).
            foreach ($itemsData as $item) {
                $products->get($item['product_id'])->decrement('stock', $item['qty']);
            }

            return $transaction;
        });
    }
}
