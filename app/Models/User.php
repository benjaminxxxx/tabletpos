<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'google_id', 'profile_photo_path'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
    /**
     * La cuenta de la que este usuario es dueño (máximo una).
     */
    public function ownedAccount(): HasOne
    {
        return $this->hasOne(Account::class, 'owner_id');
    }

    /**
     * Cuentas donde participa como admin o seller.
     */
    public function memberAccounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'account_users')
            ->withPivot(['role', 'is_blocked'])
            ->withTimestamps();
    }

    /**
     * TODAS las cuentas a las que tiene acceso:
     * la propia (como owner) + las que fue invitado.
     * Útil para el selector de cuenta en el login.
     */
    public function allAccounts()
    {
        $owned = $this->ownedAccount ? collect([$this->ownedAccount]) : collect();
        return $owned->merge($this->memberAccounts);
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function isOwnerOf(Account $account): bool
    {
        return $account->owner_id === $this->id;
    }

    public function isMemberOf(Account $account): bool
    {
        return $this->memberAccounts()->where('account_id', $account->id)->exists();
    }

    public function roleIn(Account $account): ?string
    {
        if ($this->isOwnerOf($account)) {
            return 'owner';
        }

        $pivot = $this->memberAccounts()
            ->where('account_id', $account->id)
            ->first()?->pivot;

        return $pivot?->role;
    }

    public function isBlockedIn(Account $account): bool
    {
        if ($this->isOwnerOf($account)) {
            return false; // el owner nunca puede ser bloqueado
        }

        return (bool) $this->memberAccounts()
            ->where('account_id', $account->id)
            ->first()?->pivot?->is_blocked;
    }

    public function hasOwnerAccount(): bool
    {
        return $this->ownedAccount()->exists();
    }
}
