<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\SaleStatus;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'transaction_number',
        'transaction_date',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'status' => SaleStatus::class,
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function rentalDetails(): HasMany
    {
        return $this->hasMany(RentalDetail::class);
    }

    public function allLineItems()
    {
        // Retorna union de detalles de venta y alquiler para reportes
        $sales = $this->saleDetails()->get();
        $rentals = $this->rentalDetails()->get();
        return $sales->merge($rentals)->sortBy('created_at');
    }

    public function calculateTotalAmount(): void
    {
        $salesTotal = $this->saleDetails()->sum('subtotal');
        $rentalsTotal = $this->rentalDetails()->sum('subtotal');
        $this->total_amount = $salesTotal + $rentalsTotal;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->calculateTotalAmount();
        });
    }
}
