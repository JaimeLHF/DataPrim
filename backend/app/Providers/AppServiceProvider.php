<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate limiter: endpoint público de webhook (por slug, isolamento multi-tenant)
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->route('slug') ?? $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Muitas requisições. Por favor, aguarde antes de tentar novamente.',
                    ], 429, $headers);
                });
        });

        // Rate limiter: rotas autenticadas (por user_id, fallback IP)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)
                ->by($request->user()?->id ?? $request->ip());
        });

        // Rate limiter: rotas admin (limite mais restritivo)
        RateLimiter::for('api-admin', function (Request $request) {
            return Limit::perMinute(30)
                ->by('admin:' . ($request->user()?->id ?? $request->ip()));
        });

        // Rate limiter: login (anti brute-force, por IP)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Muitas tentativas de login. Por favor, aguarde antes de tentar novamente.',
                    ], 429, $headers);
                });
        });
    }
}
