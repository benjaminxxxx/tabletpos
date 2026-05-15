<?php
// app/Http/Middleware/RequireActiveAccount.php

namespace App\Http\Middleware;

use App\Models\Account;
use Closure;
use Illuminate\Http\Request;

class RequireActiveAccount
{
    public function handle(Request $request, Closure $next)
    {
        $user      = $request->user();
        $accountId = session('active_account_id');

        if (! $accountId) {
            return redirect()->route('accounts.select');
        }

        $account = Account::find($accountId);

        // Cuenta eliminada o inactiva
        if (! $account || ! $account->is_active) {
            session()->forget('active_account_id');
            return redirect()->route('accounts.select')
                ->withErrors(['account' => 'La cuenta ya no está disponible.']);
        }

        // Es owner — siempre tiene acceso
        if ($account->isOwnedBy($user)) {
            $request->attributes->set('active_account', $account);
            return $next($request);
        }

        // Es miembro — verificar en tiempo real contra la BD
        $member = $account->members()
            ->where('user_id', $user->id)
            ->first();

        // Fue eliminado de la cuenta
        if (! $member) {
            session()->forget('active_account_id');
            return redirect()->route('accounts.select')
                ->withErrors(['account' => 'Ya no tienes acceso a esta cuenta.']);
        }

        // Fue bloqueado
        if ($member->pivot->is_blocked) {
            session()->forget('active_account_id');
            return redirect()->route('accounts.select')
                ->withErrors(['account' => 'Tu acceso a esta cuenta fue bloqueado.']);
        }

        $request->attributes->set('active_account', $account);
        return $next($request);
    }
}