<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /** Atualiza apenas o nome; o e-mail permanece como identificador da conta. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
        ]);

        $request->user()->update($data);

        return response()->json($request->user()->fresh());
    }

    /** Confere a senha atual antes de gravar a nova senha com hash. */
    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'A senha atual está incorreta.',
            ]);
        }

        if (Hash::check($data['password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => 'A nova senha deve ser diferente da senha atual.',
            ]);
        }

        $request->user()->update(['password' => $data['password']]);

        return response()->json(['message' => 'Senha alterada com sucesso.']);
    }

    /** Exclui permanentemente o usuário e todos os registros vinculados. */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['password' => ['required', 'string']]);

        if (! Hash::check($data['password'], $request->user()->password)) {
            throw ValidationException::withMessages(['password' => 'Senha incorreta.']);
        }

        $request->user()->delete();

        return response()->json(null, 204)->withoutCookie('finaxis_token');
    }
}
