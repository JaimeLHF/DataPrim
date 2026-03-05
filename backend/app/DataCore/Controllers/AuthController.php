<?php

namespace App\DataCore\Controllers;

use App\DataCore\Controllers\Controller;
use App\DataCore\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @group Autenticação
 *
 * Endpoints para login, logout e consulta do usuário autenticado.
 */
class AuthController extends Controller
{
    /**
     * Login
     *
     * Autentica o usuário e retorna um token Bearer para uso nas demais requisições.
     *
     * @unauthenticated
     *
     * @bodyParam email string required E-mail do usuário. Example: admin@moveisruiz.com.br
     * @bodyParam password string required Senha do usuário. Example: password
     *
     * @response 200 {"token":"1|abc123...","user":{"id":1,"name":"Admin Ruiz","email":"admin@moveisruiz.com.br"},"companies":[{"id":1,"name":"Móveis Ruiz","slug":"moveis-ruiz","plan":"professional","role":"owner"}]}
     * @response 401 {"error":"Credenciais inválidas.","message":"E-mail ou senha incorretos."}
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'error'   => 'Credenciais inválidas.',
                'message' => 'E-mail ou senha incorretos.',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        $companies = $user->companies()
            ->where('is_active', true)
            ->select('companies.id', 'companies.name', 'companies.slug', 'companies.plan')
            ->get()
            ->map(fn ($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'plan' => $c->plan,
                'role' => $c->pivot->role,
            ]);

        $token = $user->createToken('app')->plainTextToken;

        return response()->json([
            'token'     => $token,
            'user'      => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'is_admin' => $user->is_admin,
            ],
            'companies' => $companies,
        ]);
    }

    /**
     * Logout
     *
     * Revoga o token atual do usuário.
     *
     * @response 204
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    /**
     * Usuário autenticado
     *
     * Retorna dados do usuário logado e suas empresas.
     *
     * @response 200 {"user":{"id":1,"name":"Admin Ruiz","email":"admin@moveisruiz.com.br"},"companies":[{"id":1,"name":"Móveis Ruiz","slug":"moveis-ruiz","plan":"professional","role":"owner"}]}
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $companies = $user->companies()
            ->where('is_active', true)
            ->select('companies.id', 'companies.name', 'companies.slug', 'companies.plan')
            ->get()
            ->map(fn ($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'plan' => $c->plan,
                'role' => $c->pivot->role,
            ]);

        return response()->json([
            'user'      => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'is_admin' => $user->is_admin,
            ],
            'companies' => $companies,
        ]);
    }
}
