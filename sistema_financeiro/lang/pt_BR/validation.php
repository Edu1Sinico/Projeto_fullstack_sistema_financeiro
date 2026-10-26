<?php

return [
    'required' => 'O campo :attribute é obrigatório.',
    'email' => 'O campo :attribute deve conter um e-mail válido.',
    'unique' => 'Este :attribute já está cadastrado.',
    'confirmed' => 'A confirmação de :attribute não corresponde.',
    'min' => [
        'string' => 'O campo :attribute deve possuir pelo menos :min caracteres.',
        'numeric' => 'O campo :attribute deve ser no mínimo :min.',
    ],
    'max' => [
        'string' => 'O campo :attribute não pode possuir mais que :max caracteres.',
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
    ],
    'numeric' => 'O campo :attribute deve ser um número.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'date' => 'O campo :attribute deve conter uma data válida.',
    'in' => 'O valor selecionado para :attribute é inválido.',
    'gt' => [
        'numeric' => 'O campo :attribute deve ser maior que :value.',
    ],
    'after_or_equal' => 'O campo :attribute deve ser uma data posterior ou igual a :date.',
    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'password_confirmation' => 'confirmação da senha',
        'current_password' => 'senha atual',
        'type' => 'tipo',
        'status' => 'status',
        'balance' => 'saldo',
        'account_id' => 'conta',
        'category_id' => 'categoria',
        'description' => 'descrição',
        'amount' => 'valor',
        'transaction_date' => 'data da transação',
        'title' => 'nome da meta',
        'target_amount' => 'objetivo',
        'deadline' => 'prazo',
    ],
];
