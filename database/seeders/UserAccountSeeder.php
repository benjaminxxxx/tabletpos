<?php
// database/seeders/UserAccountSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAccountSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el usuario Benjamin
        $user = User::create([
            'id' => 1,
            'name' => 'benjamin',
            'email' => 'benjamin_unitek@hotmail.com',
            'email_verified_at' => null,
            // Usamos el hash exacto de tu base de datos
            'password' => Hash::make('masnaki18'), 
            'created_at' => '2026-05-15 16:02:02',
            'updated_at' => '2026-05-15 16:02:02',
        ]);

        // 2. Crear la cuenta asociada
        Account::create([
            'id' => 1,
            'name' => 'CUENTA00000001',
            'description' => null,
            'owner_id' => $user->id, // Vinculado a Benjamin
            'is_active' => true,
            'created_at' => '2026-05-15 16:02:02',
            'updated_at' => '2026-05-15 16:02:02',
        ]);
        
        $this->command->info('Usuario y Cuenta creados exitosamente.');
    }
}