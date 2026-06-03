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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            // Kasir yang memproses transaksi. restrictOnDelete agar histori
            // penjualan tidak ikut hilang saat akun user dihapus.
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->decimal('total', 12, 2);
            $table->decimal('paid', 12, 2);
            $table->decimal('change', 12, 2);
            $table->enum('payment_method', ['tunai', 'qris', 'transfer'])
                ->default('tunai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
