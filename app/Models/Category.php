<?php
// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'account_id',
        'parent_id',
        'name',
        'prefix',
        'is_global',
    ];

    protected $casts = [
        'is_global' => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ─── Scopes ───────────────────────────────────────────────

    /**
     * Categorías visibles para una cuenta:
     * las globales (seeder) + las propias de la cuenta.
     */
    public function scopeVisibleFor($query, int $accountId)
    {
        return $query->where(function ($q) use ($accountId) {
            $q->where('is_global', true)
              ->orWhere('account_id', $accountId);
        });
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
