<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    /**
     * @param array<string, mixed> $input
     * @param array<string, array<int, string>> $rules
     * @return array<string, mixed>
     */
    public static function validate(array $input, array $rules): array
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $fieldRules) {
            $exists = array_key_exists($field, $input);
            $value = $input[$field] ?? null;
            $optional = in_array('sometimes', $fieldRules, true);
            $nullable = in_array('nullable', $fieldRules, true);

            if (!$exists && $optional) {
                continue;
            }
            if ((!$exists || $value === '' || $value === null) && in_array('required', $fieldRules, true)) {
                $errors[$field][] = 'O campo '.$field.' é obrigatório.';
                continue;
            }
            if (($value === '' || $value === null) && $nullable) {
                $validated[$field] = null;
                continue;
            }
            if (!$exists) {
                continue;
            }

            foreach ($fieldRules as $rule) {
                [$name, $argument] = array_pad(explode(':', $rule, 2), 2, null);
                $invalid = match ($name) {
                    'email' => filter_var($value, FILTER_VALIDATE_EMAIL) === false,
                    'string' => !is_string($value),
                    'integer' => filter_var($value, FILTER_VALIDATE_INT) === false,
                    'numeric' => !is_numeric($value),
                    'array' => !is_array($value),
                    'date' => !is_string($value) || strtotime($value) === false,
                    'min' => is_numeric($value) && !is_string($value) ? (float) $value < (float) $argument : self::length((string) $value) < (int) $argument,
                    'max' => is_numeric($value) && !is_string($value) ? (float) $value > (float) $argument : self::length((string) $value) > (int) $argument,
                    'gt' => !is_numeric($value) || (float) $value <= (float) $argument,
                    'in' => !in_array((string) $value, explode(',', (string) $argument), true),
                    'confirmed' => $value !== ($input[$field.'_confirmation'] ?? null),
                    default => false,
                };

                if ($invalid) {
                    $errors[$field][] = self::message($field, $name, $argument);
                    break;
                }
            }

            if (!isset($errors[$field])) {
                $validated[$field] = $value;
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $validated;
    }

    private static function message(string $field, string $rule, ?string $argument): string
    {
        return match ($rule) {
            'email' => 'Informe um e-mail válido.',
            'min' => "O campo {$field} deve possuir no mínimo {$argument} caracteres.",
            'max' => "O campo {$field} deve possuir no máximo {$argument} caracteres.",
            'gt' => "O campo {$field} deve ser maior que {$argument}.",
            'in' => "O valor informado para {$field} é inválido.",
            'confirmed' => 'A confirmação não corresponde.',
            'date' => "Informe uma data válida para {$field}.",
            default => "O campo {$field} é inválido.",
        };
    }

    private static function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
