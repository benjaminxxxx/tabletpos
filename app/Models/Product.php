<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    protected $casts = [
        'can_sell' => 'boolean',
        'can_rent' => 'boolean',
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

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
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
            'available' => 'green',
            'rented' => 'amber',
            'blocked' => 'red',
            'laundry' => 'blue',
            'maintenance' => 'gray',
            default => 'gray',
        };
    }
}
