<?php

namespace Database\Seeders;

use App\DataCore\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'cnpj'    => '24.287.808/0001-60',
                'name'    => 'Móveis Ruiz',
                'region'  => 'Sul',
                'segment' => 'Moveleiro',
                'state'   => 'SC',
                'slug'    => 'moveis-ruiz',
                'plan'    => 'pro',
            ],
            [
                'cnpj'    => '00.000.001/0001-01',
                'name'    => 'Empresa X',
                'region'  => 'Sudeste',
                'segment' => 'Moveleiro',
                'state'   => 'SP',
                'slug'    => 'empresa-x',
                'plan'    => 'starter',
            ],
            [
                'cnpj'    => '11.222.333/0001-44',
                'name'    => 'Madeiras Silva Ltda',
                'region'  => 'Sul',
                'segment' => 'Moveleiro',
                'state'   => 'PR',
                'slug'    => 'madeiras-silva',
                'plan'    => 'trial',
            ],
            [
                'cnpj'    => '55.666.777/0001-88',
                'name'    => 'Design Móveis S.A.',
                'region'  => 'Sudeste',
                'segment' => 'Moveleiro',
                'state'   => 'MG',
                'slug'    => 'design-moveis',
                'plan'    => 'starter',
            ],
            [
                'cnpj'    => '99.888.777/0001-66',
                'name'    => 'MovelarBR Indústria',
                'region'  => 'Centro-Oeste',
                'segment' => 'Moveleiro',
                'state'   => 'GO',
                'slug'    => 'movelar-br',
                'plan'    => 'trial',
            ],
        ];

        foreach ($companies as $data) {
            Company::updateOrCreate(
                ['cnpj' => $data['cnpj']],
                array_merge($data, ['is_active' => true]),
            );
        }
    }
}
