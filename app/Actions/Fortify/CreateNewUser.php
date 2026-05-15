<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Account;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $this->validate($input);

        $existingUser = User::where('email', $input['email'])->first();

        if ($existingUser) {
            $this->ensureUserCanCreateAccount($existingUser, $input['password']);
        }

        return DB::transaction(function () use ($input, $existingUser) {
            $user = $existingUser ?? $this->createUser($input);

            // Crear cuenta — el owner queda registrado en accounts.owner_id
            // No hay attach() a account_users: el owner es implícito
            $account = Account::create([
                'name' => 'CUENTA-NUEVA',
                'owner_id' => $user->id,
                'is_active' => true,
            ]);

            $account->update([
                'name' => 'CUENTA' . str_pad($account->id, 8, '0', STR_PAD_LEFT),
            ]);

            return $user;
        });
    }

    private function validate(array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['sometimes', ...$this->passwordRules()],
        ])->validate();
    }

    private function ensureUserCanCreateAccount(User $user, string $password): void
    {
        if ($user->hasOwnerAccount()) {
            throw ValidationException::withMessages([
                'email' => ['Este correo ya es propietario de una cuenta.'],
            ]);
        }

        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Contraseña incorrecta para este correo.'],
            ]);
        }
    }

    private function createUser(array $input): User
    {
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
