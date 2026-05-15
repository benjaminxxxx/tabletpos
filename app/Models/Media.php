<?php
// app/Models/Media.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'uploaded_by',
        'path',
        'path_thumb',
        'path_full',
        'disk',
        'type',
        'mime_type',
        'size',
        'original_name',
        'category_id',
        'color',
        'brand',
        'material',
        'gender',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_media')
            ->withPivot('sort_order')
            ->withTimestamps();
    }


    // URL de thumbnail — para grids y listas
    public function getThumbUrlAttribute(): string
    {
        $path = $this->path_thumb ?? $this->path;
        return Storage::disk($this->disk)->url($path);
    }

    // URL full — para vista detalle y lightbox
    public function getFullUrlAttribute(): string
    {
        $path = $this->path_full ?? $this->path;
        return Storage::disk($this->disk)->url($path);
    }

    // Alias genérico — usa thumb por defecto (más seguro para rendimiento)
    public function getUrlAttribute(): string
    {
        return $this->thumb_url;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'video/');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Buscar media que coincida dinámicamente con atributos del producto.
     * Sin FK — matching por valores string.
     */
    public static function matchingProduct(Product $product, int $limit = 20)
    {
        return static::where('account_id', $product->account_id)
            ->where('status', 'approved')
            ->where(function ($q) use ($product) {
                $q->where(function ($q) use ($product) {
                    // Match exacto: misma categoría + color + marca
                    if ($product->category_id) {
                        $q->where('category_id', $product->category_id);
                    }
                    if ($product->color) {
                        $q->where('color', $product->color);
                    }
                    if ($product->brand) {
                        $q->where('brand', $product->brand);
                    }
                })->orWhere(function ($q) use ($product) {
                    // Match parcial: solo categoría + color
                    if ($product->category_id) {
                        $q->where('category_id', $product->category_id);
                    }
                    if ($product->color) {
                        $q->where('color', $product->color);
                    }
                })->orWhere(function ($q) use ($product) {
                    // Match mínimo: solo categoría
                    if ($product->category_id) {
                        $q->where('category_id', $product->category_id);
                    }
                });
            })
            ->orderByRaw("
                CASE
                    WHEN category_id = ? AND color = ? AND brand = ? THEN 1
                    WHEN category_id = ? AND color = ? THEN 2
                    WHEN category_id = ? THEN 3
                    ELSE 4
                END
            ", [
                $product->category_id,
                $product->color,
                $product->brand,
                $product->category_id,
                $product->color,
                $product->category_id,
            ])
            ->limit($limit)
            ->get();
    }
}