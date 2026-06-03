<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['category_id', 'name', 'sku', 'price', 'stock', 'image', 'is_active'])]
class Product extends Model
{
    use HasFactory;

    /** Batas stok menipis (PRD 4.3): stok < 5 ditandai. */
    public const LOW_STOCK_THRESHOLD = 5;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** True bila stok sudah di bawah ambang dan perlu diisi ulang. */
    public function isLowStock(): bool
    {
        return $this->stock < self::LOW_STOCK_THRESHOLD;
    }
}
