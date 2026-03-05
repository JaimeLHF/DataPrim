<?php

namespace Database\Seeders;

use App\DataCore\Models\CategoryMaster;
use Illuminate\Database\Seeder;

class CategoryMasterSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug'        => 'chapa',
                'name'        => 'Chapa / MDF',
                'group'       => 'Matéria-Prima',
                'description' => 'Chapas de MDF, compensado, aglomerado e derivados de madeira.',
                'sort_order'  => 1,
            ],
            [
                'slug'        => 'ferragem',
                'name'        => 'Ferragem',
                'group'       => 'Insumo',
                'description' => 'Dobradiças, parafusos, corrediças, minifix e fixadores em geral.',
                'sort_order'  => 2,
            ],
            [
                'slug'        => 'quimico',
                'name'        => 'Químico',
                'group'       => 'Insumo',
                'description' => 'Colas, tintas, seladores, vernizes e produtos químicos.',
                'sort_order'  => 3,
            ],
            [
                'slug'        => 'aramado',
                'name'        => 'Aramado',
                'group'       => 'Insumo',
                'description' => 'Arames, telas, grades e suportes aramados.',
                'sort_order'  => 4,
            ],
            [
                'slug'        => 'embalagem',
                'name'        => 'Embalagem',
                'group'       => 'Insumo',
                'description' => 'Caixas de papelão, plásticos, fitas e materiais de embalagem.',
                'sort_order'  => 5,
            ],
            [
                'slug'        => 'acessorio',
                'name'        => 'Acessório',
                'group'       => 'Insumo',
                'description' => 'Puxadores, rodízios, perfis, bordas e acabamentos.',
                'sort_order'  => 6,
            ],
            [
                'slug'        => 'frete',
                'name'        => 'Frete',
                'group'       => 'Logística',
                'description' => 'Custo de transporte e frete sobre compras.',
                'sort_order'  => 7,
            ],
            [
                'slug'        => 'outros',
                'name'        => 'Outros',
                'group'       => null,
                'description' => 'Categorias não classificadas ou materiais diversos.',
                'sort_order'  => 99,
            ],
        ];

        foreach ($categories as $cat) {
            CategoryMaster::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }
}
