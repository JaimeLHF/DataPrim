<?php

namespace App\DataCore\Controllers;

use App\DataCore\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Empresas
 *
 * Empresas vinculadas ao usuário autenticado.
 */
class CompanyController extends Controller
{
    /**
     * Listar empresas
     *
     * Retorna todas as empresas ativas vinculadas ao usuário.
     *
     * @response 200 {"data":[{"id":1,"name":"Móveis Ruiz","slug":"moveis-ruiz","plan":"professional","role":"owner"}]}
     */
    public function index(Request $request): JsonResponse
    {
        $companies = $request->user()
            ->companies()
            ->where('is_active', true)
            ->select('companies.id', 'companies.name', 'companies.slug', 'companies.plan')
            ->orderBy('companies.name')
            ->get()
            ->map(fn ($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'plan' => $c->plan,
                'role' => $c->pivot->role,
            ]);

        return response()->json(['data' => $companies]);
    }
}
