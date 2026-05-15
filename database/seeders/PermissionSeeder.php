<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Crear permisos
        $permissions = [
            'sell',              // Vender productos
            'rent',              // Gestionar rentals
            'manage-products',   // Registrar/editar productos
            'view-reports',      // Ver reportes
            'cash-close',        // Cierre de caja
            'manage-users',      // Gestionar usuarios (admin only)
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        
        // Admin: Acceso a TODO
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        // Seller: Venta, rentals, productos, reportes
        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $sellerRole->syncPermissions(['sell', 'rent', 'manage-products', 'view-reports']);

        // Viewer: Solo reportes y dashboard
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);
        $viewerRole->syncPermissions(['view-reports']);
    }
}
