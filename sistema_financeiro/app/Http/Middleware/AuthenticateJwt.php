<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticateJwt
{
    public function __construct(private readonly JwtService $jwt) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->cookie('finaxis_token');

        if ($token === null) {
            return $this->unauthorized($request, 'Token não informado.');
        }

        try {
            $payload = $this->jwt->decode($token);
            $userId = filter_var($payload->sub ?? null, FILTER_VALIDATE_INT);
            $user = $userId === false ? null : User::query()->find($userId);
        } catch (Throwable) {
            return $this->unauthorized($request, 'Token inválido ou expirado.');
        }

        if ($user === null) {
            return $this->unauthorized($request, 'Usuário do token não encontrado.');
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    private function unauthorized(Request $request, string $message): Response
    {
        if (! $request->is('api/*') && ! $request->expectsJson()) {
            return redirect()->route('login')->with('error', $message);
        }

        return response()->json(['message' => $message], 401);
    }
}
