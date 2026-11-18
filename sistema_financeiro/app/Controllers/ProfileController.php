<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\User;

/** Permite editar nome e senha ou excluir definitivamente o perfil autenticado. */
final class ProfileController extends Controller
{
    public function update(Request $r): never
    {
        $d = Validator::validate($r->all(), ['name' => ['required', 'string', 'min:3', 'max:100']]);
        Response::json(User::publicData(User::updateName($this->userId($r), $d['name'])));
    }
    public function changePassword(Request $r): never
    {
        $d = Validator::validate($r->all(), ['current_password' => ['required', 'string'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        $u = $r->user();
        if (!password_verify($d['current_password'], $u['password'])) {
            throw new ValidationException(['current_password' => ['A senha atual está incorreta.']]);
        }
        if (password_verify($d['password'], $u['password'])) {
            throw new ValidationException(['password' => ['A nova senha deve ser diferente da senha atual.']]);
        }
        User::updatePassword($this->userId($r), $d['password']);
        Response::json(['message' => 'Senha alterada com sucesso.']);
    }
    public function destroy(Request $r): never
    {
        $d = Validator::validate($r->all(), ['password' => ['required', 'string']]);
        if (!password_verify($d['password'], $r->user()['password'])) {
            throw new ValidationException(['password' => ['Senha incorreta.']]);
        }
        User::delete($this->userId($r));
        setcookie('finaxis_token', '', ['expires' => time() - 3600, 'path' => base_url() ?: '/', 'httponly' => true, 'samesite' => 'Lax']);
        Response::json(null, 204);
    }
}
