<?php
// app/Http/Controllers/AccountController.php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function select(Request $request)
    {
        // Limpiar siempre la cuenta activa al llegar aquí
        // Así un reload fuerza volver a elegir
        session()->forget('active_account_id');

        $user = auth()->user();

        $ownedAccount = $user->ownedAccount?->is_active
            ? $user->ownedAccount
            : null;

        $memberAccounts = $user->memberAccounts()
            ->wherePivot('is_blocked', false)
            ->where('accounts.is_active', true)
            ->get();

        $allAccounts = collect();

        if ($ownedAccount) {
            $allAccounts->push($ownedAccount);
        }

        $allAccounts = $allAccounts->merge($memberAccounts);

        // Sin cuentas disponibles
        if ($allAccounts->isEmpty()) {
            return view('accounts.select', [
                'title'          => __('Sin cuentas disponibles'),
                'ownedAccount'   => null,
                'memberAccounts' => collect(),
            ]);
        }

        // Una sola cuenta — entrar directo sin mostrar selector
        if ($allAccounts->count() === 1) {
            session(['active_account_id' => $allAccounts->first()->id]);
            return redirect()->intended(route('dashboard'));
        }

        // Varias cuentas — mostrar selector
        return view('accounts.select', [
            'title'          => __('Seleccionar cuenta'),
            'ownedAccount'   => $ownedAccount,
            'memberAccounts' => $memberAccounts,
        ]);
    }

    public function setActive(Request $request)
    {
        $request->validate(['account_id' => ['required', 'integer']]);

        $user      = auth()->user();
        $accountId = (int) $request->account_id;

        $isOwner = $user->ownedAccount?->id === $accountId
            && $user->ownedAccount->is_active;

        $isMember = $user->memberAccounts()
            ->where('account_id', $accountId)
            ->wherePivot('is_blocked', false)
            ->where('accounts.is_active', true)
            ->exists();

        abort_unless($isOwner || $isMember, 403);

        session(['active_account_id' => $accountId]);

        return redirect()->intended(route('dashboard'));
    }
}