<?php

namespace App\DataCore\Controllers;

use App\DataCore\Controllers\Controller;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @group Administração
 * @subgroup Chaves de API
 *
 * Gestão de chaves de API para integração via API Push (requer role admin ou superior).
 */
class ApiKeyController extends Controller
{
    /**
     * Listar chaves
     *
     * Retorna todas as chaves de API ativas da empresa.
     *
     * @response 200 {"data":[{"id":10,"name":"ERP Protheus","last_used_at":"2026-01-15T10:00:00Z","created_at":"2026-01-01T00:00:00Z"}]}
     */
    public function index(): JsonResponse
    {
        $companyId = app('current_company_id');

        $serviceUsers = CompanyUser::where('company_id', $companyId)
            ->whereHas('user', fn ($q) => $q->where('email', 'like', 'api-%@service.local'))
            ->with('user:id,name')
            ->get();

        $keys = $serviceUsers->map(function ($cu) {
            $token = $cu->user->tokens()->latest()->first();

            return [
                'id'           => $cu->user->id,
                'name'         => $cu->user->name,
                'last_used_at' => $token?->last_used_at,
                'created_at'   => $cu->created_at,
            ];
        });

        return response()->json(['data' => $keys]);
    }

    /**
     * Criar chave
     *
     * Gera uma nova chave de API. A chave em texto puro é retornada apenas nesta resposta.
     *
     * @bodyParam name string required Nome identificador da chave. Example: ERP Protheus
     *
     * @response 201 {"data":{"id":10,"name":"ERP Protheus","key":"1|abc123def456...","created_at":"2026-01-01T00:00:00Z"}}
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $companyId = app('current_company_id');
        $uuid = Str::uuid();

        $serviceUser = User::create([
            'name'     => "api-key:{$request->name}",
            'email'    => "api-{$companyId}-{$uuid}@service.local",
            'password' => Hash::make(Str::random(64)),
        ]);

        CompanyUser::create([
            'company_id' => $companyId,
            'user_id'    => $serviceUser->id,
            'role'       => 'analyst',
            'invited_by' => $request->user()->id,
            'joined_at'  => now(),
        ]);

        $token = $serviceUser->createToken($request->name)->plainTextToken;

        return response()->json([
            'data' => [
                'id'         => $serviceUser->id,
                'name'       => $request->name,
                'key'        => $token,
                'created_at' => $serviceUser->created_at,
            ],
        ], 201);
    }

    /**
     * Revogar chave
     *
     * Remove a chave de API e todos os tokens associados.
     *
     * @urlParam id integer required ID da chave (user_id do service user). Example: 10
     *
     * @response 204
     */
    public function destroy(int $id): JsonResponse
    {
        $companyId = app('current_company_id');

        $cu = CompanyUser::where('company_id', $companyId)
            ->where('user_id', $id)
            ->whereHas('user', fn ($q) => $q->where('email', 'like', 'api-%@service.local'))
            ->firstOrFail();

        $cu->user->tokens()->delete();
        $cu->delete();

        return response()->json(null, 204);
    }
}
