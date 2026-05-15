<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'color',
        'size',
        'material',
        'location_name',
        'purchase_price',
        'purchase_date',
        'product_type',  // sellable | rentable | stock_only | asset
        'status',
        'stock',
        'can_sell',
        'can_rent',
        'rent_count',
        'sale_count',
        'category_id',
        'gender'
    ];

    protected $casts = [
        'can_sell' => 'boolean',
        'can_rent' => 'boolean',
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
    ];
    public static function generateSmartCode(int $accountId, int $categoryId, string $gender): string
    {
        $category = Category::find($categoryId);
        $prefix = ($category ? strtoupper($category->prefix) : 'XX') . strtolower($gender);

        // Buscamos el último código que empiece con ese prefijo de 3 letras (ej: ZAM)
        $last = static::withTrashed()
            ->where('account_id', $accountId)
            ->where('public_code', 'like', $prefix . '%')
            ->orderByDesc('public_code')
            ->value('public_code');

        $numeric = $last ? (int) substr($last, 3) : 0;
        return $prefix . str_pad($numeric + 1, 4, '0', STR_PAD_LEFT);
    }
    // ─── Relaciones ───────────────────────────────────────────

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_media')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'available' => 'green',
            'rented' => 'amber',
            'blocked' => 'red',
            'laundry' => 'blue',
            'maintenance' => 'gray',
            default => 'zinc',
        };
    }

    public function isEditable(): bool
    {
        // No editable si ya fue vendido o está en alquiler activo
        return !in_array($this->status, ['blocked'])
            && $this->sales()->whereIn('status', ['completed'])->doesntExist();
    }

    public function isAsset(): bool
    {
        return $this->product_type === 'asset';
    }

    public function isStockOnly(): bool
    {
        return $this->product_type === 'stock_only';
    }

    // Genera el siguiente código disponible para un prefix dado
    public static function nextCodeForPrefix(string $prefix, int $accountId): string
    {
        $last = static::withTrashed()
            ->where('account_id', $accountId)
            ->where('public_code', 'like', $prefix . '%')
            ->orderByDesc('public_code')
            ->value('public_code');

        if (!$last) {
            return $prefix . '0001';
        }

        $numeric = (int) substr($last, strlen($prefix));
        return $prefix . str_pad($numeric + 1, 4, '0', STR_PAD_LEFT);
    }
}