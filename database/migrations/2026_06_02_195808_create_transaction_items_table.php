<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            // Jika transaksi dihapus, item-itemnya ikut terhapus (cascade).
            $table->foreignId('transaction_id')
                ->constrained()
                ->cascadeOnDelete();
            // Produk boleh dihapus di kemudian hari -> restrict agar histori
            // tetap valid. Nama & harga juga disimpan sebagai snapshot di bawah.
            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();
            // SNAPSHOT: nama & harga saat transaksi terjadi (PRD catatan model data).
            // Disimpan terpisah agar riwayat akurat meski produk diubah nanti.
            $table->string('product_name');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('qty');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
