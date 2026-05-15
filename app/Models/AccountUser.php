<?php
// app/Models/AccountUser.php  (modelo del pivot — opcional pero útil)

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountUser extends Pivot
{
    protected $table = 'account_users';

    public $incrementing = true; // porque tiene $table->id()

    protected $fillable = [
        'account_id',
        'user_id',
        'role',
        'is_blocked',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
    ];
}