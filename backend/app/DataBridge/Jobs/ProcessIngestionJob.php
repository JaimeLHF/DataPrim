<?php

namespace App\DataBridge\Jobs;

use App\DataCore\Models\RawIngestion;
use App\DataForge\Services\NormalizationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessIngestionJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public array $backoff = [30, 120];

    public function __construct(
        public RawIngestion $ingestion,
    ) {}

    public function handle(NormalizationService $service): void
    {
        $service->process($this->ingestion);
    }

    public function failed(Throwable $exception): void
    {
        $this->ingestion->markAsFailed($exception->getMessage());
    }
}
