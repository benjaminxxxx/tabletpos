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
            [
                'name' => 'TERNOS Y TRAJES',
                'prefix' => 'TE',
                'children' => [
                    ['name' => 'ESMOQUIN (SMOKING)', 'prefix' => 'SM'],
                    ['name' => 'FRAC', 'prefix' => 'FR'],
                    ['name' => 'LEVITA', 'prefix' => 'LE'],
                    ['name' => 'TERNO CLÁSICO', 'prefix' => 'TC'],
                    ['name' => 'BLAZER GALA', 'prefix' => 'BZ'],
                ]
            ],
            [
                'name' => 'VESTIDOS DE GALA',
                'prefix' => 'VE',
                'children' => [
                    ['name' => 'VESTIDO DE NOCHE (LARGO)', 'prefix' => 'VL'],
                    ['name' => 'VESTIDO DE CÓCTEL (CORTO)', 'prefix' => 'VC'],
                    ['name' => 'VESTIDO DE NOVIA', 'prefix' => 'VN'],
                    ['name' => 'VESTIDO DE QUINCEAÑERA', 'prefix' => 'VQ'],
                    ['name' => 'ENTERIZOS DE GALA', 'prefix' => 'EG'],
                ]
            ],
            [
                'name' => 'PRENDAS SUPERIORES',
                'prefix' => 'PS',
                'children' => [
                    ['name' => 'CAMISA DE GALA', 'prefix' => 'CA'],
                    ['name' => 'CHALECO', 'prefix' => 'CH'],
                    ['name' => 'CORSET', 'prefix' => 'CO'],
                    ['name' => 'ABRIGO / BOLERO', 'prefix' => 'AB'],
                ]
            ],
            [
                'name' => 'CALZADO',
                'prefix' => 'ZA',
                'children' => [
                    ['name' => 'ZAPATO DE CHAROL', 'prefix' => 'ZC'],
                    ['name' => 'ZAPATO DE CUERO', 'prefix' => 'ZU'],
                    ['name' => 'TACONES / SANDALIAS GALA', 'prefix' => 'TA'],
                ]
            ],
            [
                'name' => 'ACCESORIOS',
                'prefix' => 'AC',
                'children' => [
                    ['name' => 'CORBATA / MICHI', 'prefix' => 'CM'],
                    ['name' => 'CORREA / FAJÍN', 'prefix' => 'CF'],
                    ['name' => 'JOYAS / TIARAS', 'prefix' => 'JO'],
                    ['name' => 'CARTERAS / CLUTCHES', 'prefix' => 'CL'],
                    ['name' => 'GEMELOS / PISACORBATA', 'prefix' => 'GE'],
                ]
            ],
        ];

        foreach ($categories as $parentData) {
            $children = $parentData['children'] ?? [];
            unset($parentData['children']);

            // is_global = true, account_id = null → visible para todos
            $parentData['is_global'] = true;
            $parentData['account_id'] = null;

            $parent = Category::create($parentData);

            foreach ($children as $childData) {
                $childData['parent_id'] = $parent->id;
                $childData['is_global'] = true;
                $childData['account_id'] = null;
                Category::create($childData);
            }
        }

        $this->command->info('Categorías globales creadas: ' . Category::count());
    }
}