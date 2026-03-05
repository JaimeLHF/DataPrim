<?php

namespace Tests\Feature;

use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testa o isolamento multi-tenant via ResolveTenant middleware.
 *
 * Cenários verificados:
 * - Usuário sem token → 401
 * - Usuário sem X-Company-Id → usa empresa padrão
 * - Usuário tenta acessar empresa da qual NÃO é membro → 403
 * - Empresa inativa → 403
 */
class ResolveTenantTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Empresa Principal',
            'cnpj'      => '12345678000190',
            'slug'      => 'empresa-principal',
            'plan'      => 'starter',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create();
        CompanyUser::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->admin->id,
            'role'       => 'admin',
            'joined_at'  => now(),
        ]);
        $this->adminToken = $this->admin->createToken('test')->plainTextToken;
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/invoices')->assertStatus(401);
    }

    public function test_user_cannot_access_company_they_dont_belong_to(): void
    {
        // Cria uma segunda empresa e um usuário que NÃO pertence à empresa principal
        $otherCompany = Company::create([
            'name'      => 'Empresa Estranha',
            'cnpj'      => '99999999000199',
            'slug'      => 'empresa-estranha',
            'plan'      => 'starter',
            'is_active' => true,
        ]);
        $outsider = User::factory()->create();
        CompanyUser::create([
            'company_id' => $otherCompany->id,
            'user_id'    => $outsider->id,
            'role'       => 'admin',
            'joined_at'  => now(),
        ]);
        $outsiderToken = $outsider->createToken('outsider')->plainTextToken;

        // Outsider tenta usar o ID da empresa principal no header
        $this->getJson('/api/v1/invoices', [
            'Authorization' => "Bearer {$outsiderToken}",
            'X-Company-Id'  => $this->company->id,
            'Accept'        => 'application/json',
        ])->assertStatus(403);
    }

    public function test_inactive_company_returns_403(): void
    {
        $inactiveCompany = Company::create([
            'name'      => 'Empresa Inativa',
            'cnpj'      => '55555555000155',
            'slug'      => 'empresa-inativa',
            'plan'      => 'starter',
            'is_active' => false,
        ]);
        CompanyUser::create([
            'company_id' => $inactiveCompany->id,
            'user_id'    => $this->admin->id,
            'role'       => 'admin',
            'joined_at'  => now(),
        ]);

        $this->getJson('/api/v1/invoices', [
            'Authorization' => "Bearer {$this->adminToken}",
            'X-Company-Id'  => $inactiveCompany->id,
            'Accept'        => 'application/json',
        ])->assertStatus(403);
    }

    public function test_valid_member_can_access_their_company(): void
    {
        $this->getJson('/api/v1/invoices', [
            'Authorization' => "Bearer {$this->adminToken}",
            'X-Company-Id'  => $this->company->id,
            'Accept'        => 'application/json',
        ])->assertStatus(200);
    }
}
