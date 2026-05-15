<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashClose extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'close_date',
        'expected_amount',
        'confirmed_amount',
        'notes',
    ];

    protected $casts = [
        'close_date' => 'date',
        'expected_amount' => 'decimal:2',
        'confirmed_amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasDiscrepancy(): bool
    {
        if (!$this->confirmed_amount) {
            return false;
        }
        return $this->confirmed_amount != $this->expected_amount;
    }

    public function getDiscrepancyAmount(): ?float
    {
        if (!$this->hasDiscrepancy()) {
            return null;
        }
        return $this->confirmed_amount - $this->expected_amount;
    }
}
