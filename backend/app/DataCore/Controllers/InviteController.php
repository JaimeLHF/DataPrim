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
 * @subgroup Membros
 *
 * Gestão de membros da empresa (requer role admin ou superior).
 */
class InviteController extends Controller
{
    /**
     * Listar membros
     *
     * Retorna todos os membros vinculados à empresa atual.
     *
     * @response 200 {"data":[{"id":1,"name":"Admin Ruiz","email":"admin@moveisruiz.com.br","role":"owner","joined_at":"2026-01-01T00:00:00Z"}]}
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = app('current_company_id');

        $members = CompanyUser::where('company_id', $companyId)
            ->with('user:id,name,email')
            ->get()
            ->map(fn ($cu) => [
                'id'        => $cu->user->id,
                'name'      => $cu->user->name,
                'email'     => $cu->user->email,
                'role'      => $cu->role,
                'joined_at' => $cu->joined_at,
            ]);

        return response()->json(['data' => $members]);
    }

    /**
     * Convidar membro
     *
     * Adiciona um novo membro à empresa. Se o e-mail não existir, cria o usuário automaticamente.
     *
     * @bodyParam email string required E-mail do novo membro. Example: analista@empresa.com
     * @bodyParam name string required Nome do membro. Example: Maria Analista
     * @bodyParam role string required Role do membro: admin, analyst ou viewer. Example: analyst
     *
     * @response 201 {"data":{"id":5,"name":"Maria Analista","email":"analista@empresa.com","role":"analyst"}}
     * @response 422 {"error":"Usuário já é membro.","message":"Este e-mail já está vinculado a esta empresa."}
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'name'  => 'required|string|max:255',
            'role'  => 'required|in:admin,analyst,viewer',
        ]);

        $companyId = app('current_company_id');
        $inviterId = $request->user()->id;

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $alreadyLinked = CompanyUser::where('company_id', $companyId)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyLinked) {
                return response()->json([
                    'error'   => 'Usuário já é membro.',
                    'message' => 'Este e-mail já está vinculado a esta empresa.',
                ], 422);
            }
        } else {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make(Str::random(32)),
            ]);
        }

        CompanyUser::create([
            'company_id' => $companyId,
            'user_id'    => $user->id,
            'role'       => $request->role,
            'invited_by' => $inviterId,
            'joined_at'  => now(),
        ]);

        return response()->json([
            'data' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $request->role,
            ],
        ], 201);
    }
}
