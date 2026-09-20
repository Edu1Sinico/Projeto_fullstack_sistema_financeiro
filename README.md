# Sistema Financeiro — API Laravel

API didática para controle de contas, categorias, transações e metas financeiras. O projeto usa uma estrutura Laravel simples: Models Eloquent, Controllers e rotas REST. A autenticação do front-end é feita por JWT.

## Requisitos

- PHP 8.3 ou superior
- Composer
- PostgreSQL para o desenvolvimento
- MySQL 8.4 para validar a entrega final

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Preencha `JWT_SECRET` no `.env` com uma chave aleatória de pelo menos 32 caracteres. Uma forma de gerar a chave é:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Crie o banco configurado no `.env` e execute:

```bash
php artisan migrate
php artisan serve
```

O PostgreSQL de desenvolvimento também pode ser iniciado com:

```bash
docker compose up -d postgres
```

Para iniciar o MySQL configurado no perfil alternativo:

```bash
docker compose --profile mysql up -d mysql
```

## Autenticação JWT

Rotas públicas:

| Método | Rota | Finalidade |
|---|---|---|
| POST | `/api/auth/register` | Cadastrar usuário |
| POST | `/api/auth/login` | Autenticar usuário |

Rotas protegidas:

| Método | Rota | Finalidade |
|---|---|---|
| GET | `/api/auth/me` | Consultar usuário autenticado |
| POST | `/api/auth/refresh` | Gerar um token renovado |
| POST | `/api/auth/logout` | Orientar o front-end a remover o token |

Envie o token nas rotas protegidas:

```http
Authorization: Bearer SEU_TOKEN
```

O JWT é stateless. Portanto, o logout remove o token no front-end; o servidor não mantém sessões nem lista de tokens revogados.

## Recursos financeiros

- `/api/accounts`: CRUD de contas.
- `/api/categories`: CRUD de categorias.
- `/api/transactions`: CRUD de receitas e despesas, com filtros por conta, tipo e período.
- `/api/goals`: CRUD de metas.
- `/api/goals/{goal}/contributions`: cria ou atualiza uma contribuição.
- `/api/goals/{goal}/contributions/{transaction}`: remove uma contribuição.
- `/api/dashboard`: resumo de saldo, receitas, despesas, contas e metas.

Tipos de conta aceitos:

- `checking`: conta bancária
- `savings`: poupança
- `wallet`: carteira

Tipos de categoria e transação:

- `income`: receita
- `expense`: despesa

## Regras principais

- Todos os registros são limitados ao usuário identificado pelo JWT.
- Receitas aumentam o saldo e despesas diminuem o saldo.
- Uma despesa não pode deixar a conta com saldo negativo.
- Alterar ou excluir uma transação reverte seu efeito anterior no saldo.
- Categoria e transação devem possuir o mesmo tipo.
- Contribuições não podem superar o valor da transação.
- O progresso da meta é calculado pela soma das contribuições; não é armazenado de forma duplicada.
- Operações de saldo usam transações de banco e bloqueio do registro da conta.

## Testes

Por padrão, `phpunit.xml` utiliza SQLite em memória. Se o PHP local não possuir esse driver, informe uma conexão de testes PostgreSQL ou MySQL pelas variáveis de ambiente antes de executar:

```bash
php artisan test
```

Nunca execute a suíte contra um banco com dados importantes: os testes recriam as tabelas.

Verificações completas:

```bash
composer lint:check
composer types:check
php artisan test
```

## Estrutura principal

```text
app/
├── Http/Controllers/
├── Http/Middleware/AuthenticateJwt.php
├── Models/
└── Services/JwtService.php

database/migrations/
routes/api.php
tests/Feature/
```
