<?php

namespace Tests\Feature;

use App\DataBridge\Jobs\ProcessIngestionJob;
use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\Invoice;
use App\DataCore\Models\RawIngestion;
use App\DataCore\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ImportJsonTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Company $company;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Empresa Teste',
            'cnpj'      => '12345678000190',
            'slug'      => 'empresa-teste',
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

        $this->token = $this->admin->createToken('test')->plainTextToken;
    }

    private function headers(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'X-Company-Id'  => $this->company->id,
            'Accept'        => 'application/json',
        ];
    }

    private function validPayload(string $invoiceNumber = 'NF-TEST-001'): array
    {
        return [
            'source'   => 'oracle_erp',
            'invoices' => [
                [
                    'invoice_number' => $invoiceNumber,
                    'issue_date'     => '2026-01-15',
                    'delivery_date'  => '2026-01-20',
                    'payment_terms'  => 30,
                    'supplier'       => [
                        'cnpj'  => '98765432000155',
                        'name'  => 'Fornecedor Exemplo SA',
                        'state' => 'PR',
                    ],
                    'items' => [
                        [
                            'description' => 'MDF 18mm Branco',
                            'quantity'    => 100,
                            'unit_price'  => 45.50,
                            'category'    => 'MDF',
                        ],
                    ],
                    'totals' => [
                        'goods'   => 4550.00,
                        'freight' => 200.00,
                        'tax'     => 386.75,
                        'total'   => 5136.75,
                    ],
                ],
            ],
        ];
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/invoices/import-json', $this->validPayload());
        $response->assertStatus(401);
    }

    public function test_it_accepts_valid_json_payload_and_dispatches_job(): void
    {
        Queue::fake();

        $response = $this->postJson(
            '/api/v1/invoices/import-json',
            $this->validPayload(),
            $this->headers()
        );

        $response->assertStatus(202)
            ->assertJsonPath('ingestions.0.skipped', false)
            ->assertJsonPath('ingestions.0.status', 'pending')
            ->assertJsonStructure([
                'message',
                'ingestions' => [['invoice_number', 'ingestion_id', 'status', 'skipped']],
            ]);

        Queue::assertPushed(ProcessIngestionJob::class);

        $this->assertDatabaseHas('raw_ingestions', [
            'company_id' => $this->company->id,
            'channel'    => 'api_push',
            'source'     => 'oracle_erp',
            'status'     => 'pending',
        ]);
    }

    public function test_it_processes_ingestion_and_persists_canonical_data(): void
    {
        // QUEUE_CONNECTION=sync nos testes → job roda imediatamente
        $response = $this->postJson(
            '/api/v1/invoices/import-json',
            $this->validPayload(),
            $this->headers()
        );

        $response->assertStatus(202);

        $ingestionId = $response->json('ingestions.0.ingestion_id');

        $this->assertDatabaseHas('invoices', [
            'company_id'     => $this->company->id,
            'invoice_number' => 'NF-TEST-001',
            'source_system'  => 'oracle_erp',
            'payment_terms'  => 30,
        ]);

        $this->assertDatabaseHas('suppliers', [
            'company_id' => $this->company->id,
            'cnpj'       => '98765432000155',
            'name'       => 'Fornecedor Exemplo SA',
            'state'      => 'PR',
            'region'     => 'Sul',
        ]);

        $invoice = Invoice::where('invoice_number', 'NF-TEST-001')->first();
        $this->assertNotNull($invoice);
        $this->assertCount(1, $invoice->items);

        $this->assertDatabaseHas('raw_ingestions', [
            'id'     => $ingestionId,
            'status' => 'done',
        ]);
    }

    public function test_it_skips_duplicate_payloads(): void
    {
        Queue::fake();

        $payload = $this->validPayload();

        // Primeira importação
        $this->postJson('/api/v1/invoices/import-json', $payload, $this->headers())
            ->assertStatus(202);

        // Segunda importação (mesmo payload = mesmo hash SHA-256)
        $response = $this->postJson('/api/v1/invoices/import-json', $payload, $this->headers())
            ->assertStatus(202);

        $response->assertJsonPath('ingestions.0.skipped', true);

        // Deve existir apenas 1 raw_ingestion
        $this->assertDatabaseCount('raw_ingestions', 1);
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->postJson(
            '/api/v1/invoices/import-json',
            ['source' => 'my_erp'],   // faltam invoices
            $this->headers()
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invoices']);
    }

    public function test_it_accepts_batch_of_multiple_invoices(): void
    {
        Queue::fake();

        $payload = $this->validPayload('NF-BATCH-001');
        $payload['invoices'][] = array_merge(
            $this->validPayload('NF-BATCH-002')['invoices'][0],
            ['invoice_number' => 'NF-BATCH-002']
        );

        $response = $this->postJson('/api/v1/invoices/import-json', $payload, $this->headers())
            ->assertStatus(202);

        $this->assertCount(2, $response->json('ingestions'));
        $this->assertDatabaseCount('raw_ingestions', 2);

        Queue::assertPushed(ProcessIngestionJob::class, 2);
    }

    public function test_it_returns_ingestion_status(): void
    {
        Queue::fake();

        $importResponse = $this->postJson(
            '/api/v1/invoices/import-json',
            $this->validPayload(),
            $this->headers()
        );

        $ingestionId = $importResponse->json('ingestions.0.ingestion_id');

        $statusResponse = $this->getJson(
            "/api/v1/ingestions/{$ingestionId}/status",
            $this->headers()
        );

        $statusResponse->assertStatus(200)
            ->assertJsonPath('id', $ingestionId)
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('channel', 'api_push')
            ->assertJsonStructure(['id', 'channel', 'source', 'status', 'attempts', 'created_at']);
    }
}
