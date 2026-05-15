<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'owner_id',
        'is_active',
    ];
    // ─── Relaciones ───────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Miembros invitados (admins y sellers).
     * El owner NO aparece aquí.
     */
   
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'account_users')
            ->withPivot(['role', 'is_blocked']) // ← sin esto pivot->role es null
            ->withTimestamps();
    }

    public function admins(): BelongsToMany
    {
        return $this->members()->wherePivot('role', 'admin');
    }

    public function sellers(): BelongsToMany
    {
        return $this->members()->wherePivot('role', 'seller');
    }

    public function activeSellers(): BelongsToMany
    {
        return $this->sellers()->wherePivot('is_blocked', false);
    }

    // ─── Helpers ──────────────────────────────────────────────

    /**
     * Todos los usuarios con acceso: owner + miembros.
     * Para listados de gestión.
     */
    public function allUsers()
    {
        return $this->members->prepend($this->owner->setAttribute('pivot_role', 'owner'));
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }
}
