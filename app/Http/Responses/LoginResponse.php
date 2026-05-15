<?php
// app/Http/Responses/LoginResponse.php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // Siempre limpiar cuenta activa al hacer login
        // Así cada login fuerza elegir cuenta
        session()->forget('active_account_id');

        return redirect()->route('accounts.select');
    }
}