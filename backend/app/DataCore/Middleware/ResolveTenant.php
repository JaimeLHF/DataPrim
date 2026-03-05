<?php

namespace App\DataCore\Middleware;

use App\DataCore\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = null;
        $user      = $request->user();

        // Prioridade 0: API Key (service user) — deduz a empresa automaticamente
        // Service users têm e-mail no padrão api-*@service.local e
        // pertencem a exatamente uma empresa — X-Company-Id não é necessário.
        if (!$companyId && $user && str_contains($user->email, '@service.local')) {
            $company   = $user->currentCompany();
            $companyId = $company?->id;
        }

        // Prioridade 1: Header X-Company-Id (usuários humanos / testes)
        if (!$companyId && $request->hasHeader('X-Company-Id')) {
            $companyId = (int) $request->header('X-Company-Id');
        }

        // Prioridade 2: Query param company_id (testes no browser)
        if (!$companyId && $request->query('company_id')) {
            $companyId = (int) $request->query('company_id');
        }

        // Prioridade 3: Empresa principal do usuário autenticado
        if (!$companyId && $user) {
            $company   = $user->currentCompany();
            $companyId = $company?->id;
        }

        // Valida que a empresa existe e está ativa
        if ($companyId) {
            $exists = Company::where('id', $companyId)
                ->where('is_active', true)
                ->exists();

            if (!$exists) {
                return response()->json([
                    'error'   => 'Tenant inválido ou inativo.',
                    'message' => 'A empresa informada não existe ou está desativada.',
                ], 403);
            }

            // Valida membership: usuário autenticado deve pertencer à empresa
            if ($user) {
                $role = $user->companyRole($companyId);

                if (!$role) {
                    return response()->json([
                        'error'   => 'Acesso negado.',
                        'message' => 'Você não tem acesso a esta empresa.',
                    ], 403);
                }
            }

            app()->instance('current_company_id', $companyId);
        }

        return $next($request);
    }
}
