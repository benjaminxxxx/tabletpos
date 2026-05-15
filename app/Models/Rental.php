<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'product_id',
        'customer_id',
        'public_code_snapshot',
        'description_snapshot',
        'amount',
        'deposit_amount',
        'return_date',
        'returned_at',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'return_date' => 'date',
        'returned_at' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active' && now()->gt($this->return_date);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return now()->diffInDays($this->return_date);
    }

    public function updateStatus(): void
    {
        if ($this->status === 'active' && $this->isOverdue()) {
            $this->status = 'overdue';
            $this->save();
        }
    }
}
