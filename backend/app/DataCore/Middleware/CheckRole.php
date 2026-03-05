<?php

namespace App\DataCore\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    private const HIERARCHY = [
        'owner'   => 4,
        'admin'   => 3,
        'analyst' => 2,
        'viewer'  => 1,
    ];

    public function handle(Request $request, Closure $next, string $minimumRole): Response
    {
        $user = $request->user();

        if (!$user || !app()->has('current_company_id')) {
            return response()->json([
                'error'   => 'Não autorizado.',
                'message' => 'Autenticação e empresa são obrigatórias.',
            ], 403);
        }

        $companyId = app('current_company_id');
        $userRole = $user->companyRole($companyId);

        if (!$userRole) {
            return response()->json([
                'error'   => 'Sem acesso.',
                'message' => 'Você não é membro desta empresa.',
            ], 403);
        }

        $userLevel = self::HIERARCHY[$userRole] ?? 0;
        $requiredLevel = self::HIERARCHY[$minimumRole] ?? 0;

        if ($userLevel < $requiredLevel) {
            return response()->json([
                'error'   => 'Permissão insuficiente.',
                'message' => "Esta ação requer nível '{$minimumRole}' ou superior.",
            ], 403);
        }

        return $next($request);
    }
}
