<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['invoice_number', 'user_id', 'total', 'paid', 'change', 'payment_method'])]
class Transaction extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'change' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    /** Kasir yang memproses transaksi ini. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Baris-baris item dalam transaksi (dengan snapshot nama & harga). */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
