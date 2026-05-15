<?php
// app/Http/Controllers/Auth/GoogleController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::updateOrCreate(
            ['google_id' => $googleUser->getId()],
            [
                'name'                => $googleUser->getName(),
                'email'               => $googleUser->getEmail(),
                'profile_photo_path'  => $googleUser->getAvatar(),
                'email_verified_at'   => now(),
                'password'            => bcrypt(Str::random(24)),
            ]
        );

        Auth::login($user, remember: true);

        // Limpiar cuenta activa — forzar selección
        session()->forget('active_account_id');

        return redirect()->route('accounts.select');
    }
}