<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\RentalStatus;
use App\Enums\ProductStatus;

class RentalDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'customer_id',
        'quantity',
        'unit_rental_price',
        'subtotal',
        'guarantee_amount',
        'dni_number',
        'dni_photo_url',
        'additional_photo_url',
        'rental_start_date',
        'rental_return_date',
        'observations',
        'actual_return_date',
        'product_status_after',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_rental_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'guarantee_amount' => 'decimal:2',
        'rental_start_date' => 'date',
        'rental_return_date' => 'date',
        'actual_return_date' => 'date',
        'status' => RentalStatus::class,
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function calculateSubtotal(): void
    {
        $this->subtotal = $this->quantity * $this->unit_rental_price;
    }

    public function getDaysRented(): int
    {
        $returnDate = $this->actual_return_date ?? $this->rental_return_date;
        return $this->rental_start_date->diffInDays($returnDate);
    }

    public function isOverdue(): bool
    {
        if ($this->status === RentalStatus::DEVUELTO) {
            return false;
        }
        return now()->isAfter($this->rental_return_date);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->isDirty(['quantity', 'unit_rental_price'])) {
                $model->calculateSubtotal();
            }
        });
    }
}
