<?php

namespace Database\Seeders;

use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Um owner por empresa (slug => dados do usuário).
     */
    private array $users = [
        'moveis-ruiz'   => ['name' => 'Admin Ruiz',           'email' => 'admin@moveisruiz.com.br'],
        'empresa-x'     => ['name' => 'Admin Empresa X',      'email' => 'admin@empresax.com.br'],
        'madeiras-silva' => ['name' => 'Admin Madeiras Silva', 'email' => 'admin@madeirassilva.com.br'],
        'design-moveis' => ['name' => 'Admin Design Móveis',  'email' => 'admin@designmoveis.com.br'],
        'movelar-br'    => ['name' => 'Admin MovelarBR',      'email' => 'admin@movelarbr.com.br'],
    ];

    public function run(): void
    {
        // Usuário admin interno (time Primidéias)
        User::firstOrCreate(
            ['email' => 'admin@primideias.com.br'],
            [
                'name'     => 'Admin Primidéias',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );
        $this->command->info('  Admin interno: admin@primideias.com.br (is_admin=true)');

        $companies = Company::whereIn('slug', array_keys($this->users))->get()->keyBy('slug');

        foreach ($this->users as $slug => $userData) {
            $company = $companies->get($slug);
            if (!$company) {
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name'     => $userData['name'],
                    'password' => Hash::make('password'),
                    'is_admin' => false,
                ],
            );

            CompanyUser::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'user_id'    => $user->id,
                ],
                [
                    'role'      => 'owner',
                    'joined_at' => now(),
                ],
            );

            $this->command->info("  {$company->name}: {$userData['email']} (owner)");
        }
    }
}
