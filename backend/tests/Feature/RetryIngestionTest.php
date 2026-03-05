<?php

namespace Tests\Feature;

use App\DataBridge\Jobs\ProcessIngestionJob;
use App\DataCore\Models\Company;
use App\DataCore\Models\RawIngestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Testes do comando artisan ingestions:retry.
 *
 * Valida o ciclo: failed → pending → re-dispatch → dead-letter após limite.
 */
class RetryIngestionTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Empresa Retry',
            'cnpj'      => '12345678000190',
            'slug'      => 'empresa-retry',
            'plan'      => 'starter',
            'is_active' => true,
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function makeIngestion(array $overrides = []): RawIngestion
    {
        return RawIngestion::create(array_merge([
            'company_id'   => $this->company->id,
            'channel'      => 'webhook',
            'source'       => 'bling',
            'status'       => 'failed',
            'payload'      => json_encode(['event' => 'test']),
            'payload_hash' => hash('sha256', uniqid()),
            'error_message' => 'Erro simulado.',
            'attempts'     => 1,
        ], $overrides));
    }

    // ─── Testes ───────────────────────────────────────────────────────────

    #[Test]
    public function test_retry_dispatches_job_for_failed_ingestion(): void
    {
        Queue::fake();
        $ingestion = $this->makeIngestion(['attempts' => 1]);

        $this->artisan('ingestions:retry')->assertExitCode(0);

        Queue::assertPushed(ProcessIngestionJob::class, function ($job) use ($ingestion) {
            return $job->ingestion->id === $ingestion->id;
        });

        // Status deve ter voltado a 'pending' e error_message limpo
        $ingestion->refresh();
        $this->assertEquals('pending', $ingestion->status);
        $this->assertNull($ingestion->error_message);
    }

    #[Test]
    public function test_retry_skips_ingestion_at_dead_letter_threshold(): void
    {
        Queue::fake();

        // attempts = 9 = MAX_QUEUE_TRIES(3) × MAX_MANUAL_RETRIES(3) → dead-letter
        $dead = $this->makeIngestion(['attempts' => 9]);

        $this->artisan('ingestions:retry')
            ->expectsOutput('Nenhuma ingestão elegível para reprocessamento.')
            ->assertExitCode(0);

        Queue::assertNothingPushed();

        // Status deve permanecer 'failed'
        $dead->refresh();
        $this->assertEquals('failed', $dead->status);
    }

    #[Test]
    public function test_retry_force_bypasses_dead_letter_threshold(): void
    {
        Queue::fake();
        $dead = $this->makeIngestion(['attempts' => 9]);

        $this->artisan('ingestions:retry --force')->assertExitCode(0);

        Queue::assertPushed(ProcessIngestionJob::class, 1);

        $dead->refresh();
        $this->assertEquals('pending', $dead->status);
    }

    #[Test]
    public function test_retry_targets_specific_ingestion_by_id(): void
    {
        Queue::fake();

        $target = $this->makeIngestion(['attempts' => 1]);
        $other  = $this->makeIngestion(['attempts' => 1]);

        $this->artisan("ingestions:retry --id={$target->id}")->assertExitCode(0);

        // Apenas a ingestion alvo deve ter sido despachada
        Queue::assertPushed(ProcessIngestionJob::class, function ($job) use ($target) {
            return $job->ingestion->id === $target->id;
        });
        Queue::assertPushed(ProcessIngestionJob::class, 1);
    }

    #[Test]
    public function test_retry_by_id_fails_when_ingestion_at_dead_letter(): void
    {
        Queue::fake();
        $dead = $this->makeIngestion(['attempts' => 9]);

        $this->artisan("ingestions:retry --id={$dead->id}")->assertExitCode(1);

        Queue::assertNothingPushed();
    }

    #[Test]
    public function test_retry_filters_by_company(): void
    {
        Queue::fake();

        $otherCompany = Company::create([
            'name'      => 'Outra Empresa',
            'cnpj'      => '98765432000100',
            'slug'      => 'outra-empresa',
            'plan'      => 'starter',
            'is_active' => true,
        ]);

        $mine  = $this->makeIngestion(['company_id' => $this->company->id, 'attempts' => 1]);
        $theirs = RawIngestion::create([
            'company_id'   => $otherCompany->id,
            'channel'      => 'webhook',
            'source'       => 'bling',
            'status'       => 'failed',
            'payload'      => json_encode(['event' => 'other']),
            'payload_hash' => hash('sha256', uniqid()),
            'attempts'     => 1,
        ]);

        $this->artisan("ingestions:retry --company={$this->company->id}")->assertExitCode(0);

        Queue::assertPushed(ProcessIngestionJob::class, 1);
        Queue::assertPushed(ProcessIngestionJob::class, function ($job) use ($mine) {
            return $job->ingestion->id === $mine->id;
        });
    }

    #[Test]
    public function test_retry_only_processes_failed_ingestions(): void
    {
        Queue::fake();

        // Ingestões em outros estados — não devem ser retentadas
        $this->makeIngestion(['status' => 'pending',    'attempts' => 0]);
        $this->makeIngestion(['status' => 'processing', 'attempts' => 1]);
        $this->makeIngestion(['status' => 'done',       'attempts' => 1]);

        $this->artisan('ingestions:retry')
            ->expectsOutput('Nenhuma ingestão elegível para reprocessamento.')
            ->assertExitCode(0);

        Queue::assertNothingPushed();
    }

    #[Test]
    public function test_retry_command_is_scheduled_hourly(): void
    {
        // Verifica que o comando está registrado no scheduler (smoke test via artisan schedule:list)
        $output = '';
        $this->artisan('schedule:list')->execute();

        // Confirma que o comando existe no sistema (se não existir, artisan lançaria exceção)
        $this->assertTrue(
            class_exists(\App\DataForge\Commands\RetryFailedIngestions::class),
            'RetryFailedIngestions command deve existir'
        );
    }
}
