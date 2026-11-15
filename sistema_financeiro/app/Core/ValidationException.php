<?php

declare(strict_types=1);

namespace App\Core;

final class ValidationException extends HttpException
{
    /** @param array<string, array<int, string>> $errors */
    public function __construct(public readonly array $errors)
    {
        parent::__construct(422, 'Os dados informados são inválidos.');
    }
}
