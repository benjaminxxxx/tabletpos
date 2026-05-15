<?php
// database/seeders/CategorySeeder.php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // --- CABALLEROS ---
            ['name' => 'Ternos y Trajes', 'prefix' => 'TE', 'children' => [
                ['name' => 'Esmoquin (Smoking)', 'prefix' => 'SM'],
                ['name' => 'Frac', 'prefix' => 'FR'],
                ['name' => 'Levita', 'prefix' => 'LE'],
                ['name' => 'Terno Clásico', 'prefix' => 'TC'],
                ['name' => 'Blazer Gala', 'prefix' => 'BZ'],
            ]],

            // --- DAMAS ---
            ['name' => 'Vestidos de Gala', 'prefix' => 'VE', 'children' => [
                ['name' => 'Vestido de Noche (Largo)', 'prefix' => 'VL'],
                ['name' => 'Vestido de Cóctel (Corto)', 'prefix' => 'VC'],
                ['name' => 'Vestido de Novia', 'prefix' => 'VN'],
                ['name' => 'Vestido de Quinceañera', 'prefix' => 'VQ'],
                ['name' => 'Enterizos de Gala', 'prefix' => 'EG'],
            ]],

            // --- COMPLEMENTOS ---
            ['name' => 'Prendas Superiores', 'prefix' => 'PS', 'children' => [
                ['name' => 'Camisa de Gala', 'prefix' => 'CA'],
                ['name' => 'Chaleco', 'prefix' => 'CH'],
                ['name' => 'Corset', 'prefix' => 'CO'],
                ['name' => 'Abrigo / Bolero', 'prefix' => 'AB'],
            ]],

            // --- CALZADO ---
            ['name' => 'Calzado', 'prefix' => 'ZA', 'children' => [
                ['name' => 'Zapato de Charol', 'prefix' => 'ZC'],
                ['name' => 'Zapato de Cuero', 'prefix' => 'ZU'],
                ['name' => 'Tacones / Sandalias Gala', 'prefix' => 'TA'],
            ]],

            // --- ACCESORIOS ---
            ['name' => 'Accesorios', 'prefix' => 'AC', 'children' => [
                ['name' => 'Corbata / Michi', 'prefix' => 'CM'],
                ['name' => 'Correa / Fajín', 'prefix' => 'CF'],
                ['name' => 'Joyas / Tiaras', 'prefix' => 'JO'],
                ['name' => 'Carteras / Clutches', 'prefix' => 'CL'],
                ['name' => 'Gemelos / Pisacorbata', 'prefix' => 'GE'],
            ]],
        ];

        foreach ($categories as $parentData) {
            $children = $parentData['children'] ?? [];
            unset($parentData['children']);

            $parent = Category::create($parentData);

            foreach ($children as $childData) {
                $childData['parent_id'] = $parent->id;
                Category::create($childData);
            }
        }
    }
}