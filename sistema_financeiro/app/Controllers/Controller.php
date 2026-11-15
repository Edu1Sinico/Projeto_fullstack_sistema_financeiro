<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;

abstract class Controller
{
    protected function userId(Request $request): int
    {
        return (int) $request->user()['id'];
    }

    protected function id(Request $request, string $name): int
    {
        $id = filter_var($request->param($name), FILTER_VALIDATE_INT);
        if ($id === false) {
            throw new HttpException(404, 'Registro não encontrado.');
        }

        return $id;
    }

    protected function notFound(): never
    {
        throw new HttpException(404, 'Registro não encontrado.');
    }
}
