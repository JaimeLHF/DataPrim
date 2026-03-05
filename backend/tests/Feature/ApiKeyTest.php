<?php

namespace Tests\Feature;

use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $analyst;
    private Company $company;
    private string $adminToken;
    private string $analystToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Empresa API Keys',
            'cnpj'      => '12345678000190',
            'slug'      => 'empresa-api-keys',
            'plan'      => 'starter',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['name' => 'Admin Teste']);
        CompanyUser::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->admin->id,
            'role'       => 'admin',
            'joined_at'  => now(),
        ]);
        $this->adminToken = $this->admin->createToken('admin-token')->plainTextToken;

        $this->analyst = User::factory()->create(['name' => 'Analyst Teste']);
        CompanyUser::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->analyst->id,
            'role'       => 'analyst',
            'joined_at'  => now(),
        ]);
        $this->analystToken = $this->analyst->createToken('analyst-token')->plainTextToken;
    }

    private function adminHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->adminToken}",
            'X-Company-Id'  => $this->company->id,
            'Accept'        => 'application/json',
        ];
    }

    private function analystHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->analystToken}",
            'X-Company-Id'  => $this->company->id,
            'Accept'        => 'application/json',
        ];
    }

    public function test_admin_can_create_api_key(): void
    {
        $response = $this->postJson(
            '/api/v1/api-keys',
            ['name' => 'ERP Protheus'],
            $this->adminHeaders()
        );

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'key', 'created_at']])
            ->assertJsonPath('data.name', 'ERP Protheus');

        $this->assertNotEmpty($response->json('data.key'));
    }

    public function test_analyst_cannot_create_api_key(): void
    {
        $response = $this->postJson(
            '/api/v1/api-keys',
            ['name' => 'Chave Não Autorizada'],
            $this->analystHeaders()
        );

        $response->assertStatus(403);
    }

    public function test_it_requires_name_to_create_api_key(): void
    {
        $response = $this->postJson(
            '/api/v1/api-keys',
            [],
            $this->adminHeaders()
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_admin_can_list_api_keys(): void
    {
        $this->postJson('/api/v1/api-keys', ['name' => 'Key One'], $this->adminHeaders());
        $this->postJson('/api/v1/api-keys', ['name' => 'Key Two'], $this->adminHeaders());

        $response = $this->getJson('/api/v1/api-keys', $this->adminHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'last_used_at', 'created_at']]])
            ->assertJsonCount(2, 'data');
    }


    public function test_admin_can_revoke_api_key(): void
    {
        $createResponse = $this->postJson(
            '/api/v1/api-keys',
            ['name' => 'Key Para Revogar'],
            $this->adminHeaders()
        );
        $keyId = $createResponse->json('data.id');

        $this->deleteJson("/api/v1/api-keys/{$keyId}", [], $this->adminHeaders())
            ->assertStatus(204);

        $this->getJson('/api/v1/api-keys', $this->adminHeaders())
            ->assertJsonCount(0, 'data');
    }


    public function test_generated_api_key_can_authenticate_import_json(): void
    {
        $createResponse = $this->postJson(
            '/api/v1/api-keys',
            ['name' => 'ERP Integration Key'],
            $this->adminHeaders()
        );
        $apiKey = $createResponse->json('data.key');

        $importResponse = $this->postJson('/api/v1/invoices/import-json', [
            'source'   => 'erp_via_apikey',
            'invoices' => [
                [
                    'invoice_number' => 'NF-APIKEY-001',
                    'issue_date'     => '2026-02-01',
                    'supplier'       => [
                        'cnpj'  => '11111111000111',
                        'name'  => 'Fornecedor via ApiKey',
                        'state' => 'SP',
                    ],
                    'items' => [
                        [
                            'description' => 'Produto Teste',
                            'quantity'    => 10,
                            'unit_price'  => 100.00,
                        ],
                    ],
                    'totals' => ['total' => 1000.00],
                ],
            ],
        ], [
            'Authorization' => "Bearer {$apiKey}",
            'X-Company-Id'  => $this->company->id,
            'Accept'        => 'application/json',
        ]);

        $importResponse->assertStatus(202)
            ->assertJsonPath('ingestions.0.skipped', false);
    }
}
