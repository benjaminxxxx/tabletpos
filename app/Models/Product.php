<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ProductStatus;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'location_id',
        'public_code',
        'name',
        'description',
        'brand',
        'origin',
        'category_prefix',
        'status',
        'can_sell',
        'can_rent',
        'rent_count',
        'sale_count',
        'quantity_available',
        'quantity_rented_out',
        'quantity_sold_total',
    ];

    protected $casts = [
        'can_sell' => 'boolean',
        'can_rent' => 'boolean',
        'quantity_available' => 'integer',
        'quantity_rented_out' => 'integer',
        'quantity_sold_total' => 'integer',
        'status' => ProductStatus::class,
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function rentalDetails(): HasMany
    {
        return $this->hasMany(RentalDetail::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public static function generatePublicCode(string $accountId, string $categoryPrefix): string
    {
        $lastProduct = self::where('account_id', $accountId)
            ->where('category_prefix', $categoryPrefix)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = ($lastProduct ? (int) substr($lastProduct->public_code, 2) : 0) + 1;
        return $categoryPrefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            ProductStatus::DISPONIBLE => 'green',
            ProductStatus::ALQUILADO => 'amber',
            ProductStatus::BLOQUEADO => 'red',
            ProductStatus::VENDIDO => 'blue',
            ProductStatus::PERDIDO => 'gray',
            default => 'gray',
        };
    }

    public function reduceAvailableQuantity(int $quantity): void
    {
        $this->quantity_available -= $quantity;
        $this->save();
    }

    public function increaseAvailableQuantity(int $quantity): void
    {
        $this->quantity_available += $quantity;
        $this->save();
    }

    public function increaseRentedOut(int $quantity): void
    {
        $this->quantity_rented_out += $quantity;
        $this->save();
    }

    public function decreaseRentedOut(int $quantity): void
    {
        $this->quantity_rented_out -= $quantity;
        $this->save();
    }

    public function increaseSoldTotal(int $quantity): void
    {
        $this->quantity_sold_total += $quantity;
        $this->save();
    }
}
