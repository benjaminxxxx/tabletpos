<?php
// app/Models/Media.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'account_id',
        'path',
        'disk',
        'type',
        'mime_type',
        'size',
        'original_name',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_media')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'video/');
    }
}