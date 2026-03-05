<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── ERP Connectors — Pull Ativo (Canal 4) ────────────────────────────────
// Roda a cada 10 minutos e seleciona apenas conectores vencidos.
// Cada conector define seu próprio sync_frequency (padrão 360 min = 6h).
Schedule::command('erp:sync')->everyTenMinutes();
// ─── Retry de Ingeções com Falha ────────────────────────────────────────
// Reprocessa NF-es que falharam no pipeline de normalização.
// Respeita o limite de 3 tentativas manuais (dead-letter após 9 falhas totais).
Schedule::command('ingestions:retry')->hourly();