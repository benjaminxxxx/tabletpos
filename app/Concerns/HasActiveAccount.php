<?php
// app/Concerns/HasActiveAccount.php

namespace App\Concerns;

use App\Models\Account;
use Illuminate\Support\Facades\Gate;

trait HasActiveAccount
{
    public Account $activeAccount;

    public function bootActiveAccount(): void
    {
        $accountId = session('active_account_id');

        if (! $accountId) {
            $this->redirectToSelector('Selecciona una cuenta para continuar.');
            return;
        }

        $account = Account::find($accountId);

        if (! $account || ! $account->is_active) {
            session()->forget('active_account_id');
            $this->redirectToSelector('La cuenta ya no está disponible.');
            return;
        }

        $user = auth()->user();

        if (! $account->isOwnedBy($user)) {
            $member = $account->members()
                ->where('user_id', $user->id)
                ->first();

            if (! $member) {
                session()->forget('active_account_id');
                $this->redirectToSelector('Ya no tienes acceso a esta cuenta.');
                return;
            }

            if ($member->pivot->is_blocked) {
                session()->forget('active_account_id');
                $this->redirectToSelector('Tu acceso a esta cuenta fue bloqueado.');
                return;
            }
        }

        $this->activeAccount = $account;
    }

    private function redirectToSelector(string $message): void
    {
        session()->forget('active_account_id');

        // En Livewire usamos redirect() del componente
        $this->redirectRoute('accounts.select', navigate: true);
    }

    public function currentUserRoleInAccount(): string
    {
        return auth()->user()->roleIn($this->activeAccount);
    }

    public function currentUserCanManageUsers(): bool
    {
        return Gate::allows('manage-account-users');
    }
}