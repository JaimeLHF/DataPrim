<?php

use App\DataCore\Controllers\AlertController;
use App\DataCore\Controllers\ApiKeyController;
use App\DataCore\Controllers\AuthController;
use App\DataCore\Controllers\CompanyController;
use App\DataCore\Controllers\ContactController;
use App\DataLumen\Controllers\CostStructureBenchmarkController;
use App\DataLumen\Controllers\DashboardController;
use App\DataCore\Controllers\ErpConnectorController;
use App\DataCore\Controllers\IngestionController;
use App\DataCore\Controllers\InviteController;
use App\DataCore\Controllers\InvoiceController;
use App\DataCore\Controllers\InvoiceListController;
use App\DataLumen\Controllers\SavingController;
use App\DataLumen\Controllers\SupplierAnalysisController;
use App\DataCore\Controllers\WebhookConfigController;
use App\DataCore\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes v1 - Plataforma de Inteligência de Compras
|--------------------------------------------------------------------------
| Todas as rotas vivem sob /api/v1/
*/

Route::prefix('v1')->group(function () {

    // ── Public ───────────────────────────────────────────────────────────
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:login');

    // Webhook receptor (público — chamado pelo ERP do cliente)
    Route::post('webhooks/receive/{slug}', [WebhookController::class, 'receive'])
        ->middleware('throttle:webhook');

    // Bling OAuth2 callback (público — chamado pelo Bling após autorização do usuário)
    Route::get('erp-connectors/bling/callback', [ErpConnectorController::class, 'callback']);

    // ── Authenticated ────────────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',      [AuthController::class, 'me']);

        // Companies (scoped to authenticated user)
        Route::get('companies', [CompanyController::class, 'index']);

        // Dashboard endpoints
        Route::prefix('dashboard')->group(function () {
            Route::get('kpis',             [DashboardController::class,  'kpis']);
            Route::get('tco-breakdown',    [DashboardController::class,  'tcoBreakdown']);
            Route::get('gross-vs-net',     [DashboardController::class,  'grossVsNet']);
            Route::get('price-evolution',  [DashboardController::class,  'priceEvolution']);
            Route::get('price-index',      [DashboardController::class,  'priceIndex']);
            Route::get('dispersion',       [DashboardController::class,  'dispersion']);
            Route::get('freight-impact',   [DashboardController::class,  'freightImpact']);
            Route::get('category-ranking', [DashboardController::class,  'categoryRanking']);
            Route::get('saving',           [SavingController::class,     'index']);

            // Cost structure benchmark
            Route::get('cost-structure-benchmark',                    [CostStructureBenchmarkController::class, 'index']);
            Route::get('cost-structure-benchmark/periods',            [CostStructureBenchmarkController::class, 'periods']);
            Route::get('cost-structure-benchmark/category-products',  [CostStructureBenchmarkController::class, 'categoryProducts']);
        });

        // Invoice endpoints
        Route::prefix('invoices')->group(function () {
            Route::post('preview-xml', [InvoiceController::class,     'previewXml']);
            Route::post('import-xml',  [InvoiceController::class,     'importXml']);
            Route::post('import-json', [InvoiceController::class,     'importJson']);
            Route::get('/',            [InvoiceListController::class,  'index']);
            Route::get('{id}',         [InvoiceListController::class,  'show']);
            Route::delete('{id}',      [InvoiceListController::class,  'destroy']);
        });

        // Supplier analysis
        Route::prefix('suppliers')->group(function () {
            Route::get('/',     [SupplierAnalysisController::class, 'index']);
            Route::get('{id}',  [SupplierAnalysisController::class, 'show']);
        });

        // Contacts
        Route::get('contacts', [ContactController::class, 'index']);

        // Alerts
        Route::get('alerts',   [AlertController::class,   'index']);

        // Ingestion status
        Route::get('ingestions/{id}/status', [IngestionController::class, 'status']);

        // Webhook logs
        Route::get('webhooks/logs', [WebhookController::class, 'index']);

        // ── Admin routes (owner/admin only) ─────────────────────────────
        Route::middleware(['role:admin', 'throttle:api-admin'])->group(function () {
            Route::get('invites',  [InviteController::class, 'index']);
            Route::post('invites', [InviteController::class, 'store']);

            Route::get('api-keys',         [ApiKeyController::class, 'index']);
            Route::post('api-keys',        [ApiKeyController::class, 'store']);
            Route::delete('api-keys/{id}', [ApiKeyController::class, 'destroy']);

            // Webhook configs
            Route::get('webhook-configs',          [WebhookConfigController::class, 'index']);
            Route::post('webhook-configs',         [WebhookConfigController::class, 'store']);
            Route::delete('webhook-configs/{id}',  [WebhookConfigController::class, 'destroy']);

            // ERP Connectors (Canal 4 — Pull Ativo)
            Route::get('erp-connectors',                     [ErpConnectorController::class, 'index']);
            Route::post('erp-connectors',                    [ErpConnectorController::class, 'store']);
            Route::delete('erp-connectors/{id}',             [ErpConnectorController::class, 'destroy']);
            Route::post('erp-connectors/{id}/sync',          [ErpConnectorController::class, 'sync']);
            Route::post('erp-connectors/test-connection',    [ErpConnectorController::class, 'testConnection']);
            // Bling OAuth2 — inicia o fluxo de autorização (autenticado)
            Route::get('erp-connectors/bling/authorize',     [ErpConnectorController::class, 'authorize']);
        });
    });
});
