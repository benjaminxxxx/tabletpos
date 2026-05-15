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
        'rent_count',
        'sale_count',
        'category_id',
        'gender',
        'created_by',
        'updated_by',
        'deleted_by',
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
        $prefix = ($category ? strtoupper($category->prefix) : 'XX') . strtoupper(substr($gender, 0, 1));

        // 1. Buscamos el último código base directo en la BD
        $last = static::withTrashed()
            ->where('account_id', $accountId)
            ->where('public_code', 'like', $prefix . '%')
            ->orderByDesc('public_code')
            ->value('public_code');

        $numeric = $last ? (int) substr($last, 3) : 0;

        // 2. Bucle de seguridad: si el código existe, sumamos 1 y volvemos a verificar
        do {
            $numeric++;
            $code = $prefix . str_pad($numeric, 4, '0', STR_PAD_LEFT);

            $codeExists = static::withTrashed()
                ->where('account_id', $accountId)
                ->where('public_code', $code)
                ->exists();

        } while ($codeExists); // Si existe, el bucle se repite incrementando $numeric

        return $code;
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
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_media')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    // Media dinámica — sin FK, por atributos coincidentes
    public function getDynamicMediaAttribute()
    {
        return Media::matchingProduct($this);
    }

    // Toda la media visible: primero la vinculada, luego la dinámica sin duplicar
    public function getAllVisibleMediaAttribute()
    {
        $linked = $this->media;
        $dynamic = Media::matchingProduct($this)
            ->reject(fn($m) => $linked->contains('id', $m->id));

        return $linked->merge($dynamic);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}