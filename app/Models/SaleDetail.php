<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ProductStatus;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'product_status_after',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'product_status_after' => ProductStatus::class,
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateSubtotal(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->isDirty(['quantity', 'unit_price'])) {
                $model->calculateSubtotal();
            }
        });
    }
}
