<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller
{
    /** O Laravel injeta o serviço responsável por criar os tokens JWT. */
    public function __construct(private readonly JwtService $jwt) {}

    /**
     * Cadastra o usuário e suas categorias iniciais na mesma transação de banco.
     * Se uma das gravações falhar, o cadastro inteiro é desfeito.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        return DB::transaction(function () use ($data) {
            $user = User::create($data);

            $user->categories()->createMany([
                ['name' => 'Salário', 'type' => 'income'],
                ['name' => 'Investimentos', 'type' => 'income'],
                ['name' => 'Alimentação', 'type' => 'expense'],
                ['name' => 'Moradia', 'type' => 'expense'],
                ['name' => 'Transporte', 'type' => 'expense'],
                ['name' => 'Lazer', 'type' => 'expense'],
                ['name' => 'Saúde', 'type' => 'expense'],
            ]);

            // O token é criado antes do commit. Se a configuração JWT estiver
            // inválida, a exceção desfaz também o usuário e suas categorias.
            return $this->tokenResponse($user, 201);
        });
    }

    /**
     * Confere e-mail e senha. O Hash::check compara a senha recebida com o
     * hash armazenado; a senha em texto puro nunca é salva no banco.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'E-mail ou senha incorretos.'], 401);
        }

        return $this->tokenResponse($user);
    }

    /** Retorna o usuário que o middleware JWT colocou na requisição. */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /** Gera um novo token para um usuário cujo token atual ainda é válido. */
    public function refresh(Request $request): JsonResponse
    {
        return $this->tokenResponse($request->user());
    }

    /**
     * Como o JWT é stateless, o servidor não mantém sessão para destruir.
     * O front-end deve apagar o token recebido anteriormente.
     */
    public function logout(): JsonResponse
    {
        return response()->json([
            'message' => 'Logout realizado. Remova o token no front-end.',
        ])->withoutCookie('finaxis_token');
    }

    /** Monta a resposta padronizada usada pelo cadastro e pelo login. */
    private function tokenResponse(User $user, int $status = 200): JsonResponse
    {
        $token = $this->jwt->createToken($user);

        $response = response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $this->jwt->ttlInSeconds(),
            'user' => $user,
        ], $status);

        // O cookie permite que as páginas Blade usem o mesmo JWT sem expor o
        // token ao JavaScript. O Bearer continua disponível para Postman e apps.
        return $response->withCookie(new Cookie(
            name: 'finaxis_token',
            value: $token,
            expire: time() + $this->jwt->ttlInSeconds(),
            path: '/',
            secure: app()->environment('production'),
            httpOnly: true,
            sameSite: Cookie::SAMESITE_LAX,
        ));
    }
}
