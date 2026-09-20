# Sistema Financeiro — API Laravel

API acadêmica para organização financeira pessoal. O sistema permite que cada usuário controle suas contas, categorias, receitas, despesas e metas financeiras. A autenticação é feita com JSON Web Token (JWT), deixando o backend preparado para ser consumido por um front-end separado.

O projeto prioriza uma estrutura simples e didática, baseada nos componentes principais do Laravel:

- **Models Eloquent** para representar as entidades e seus relacionamentos;
- **Controllers** para validação, regras de negócio e respostas HTTP;
- **Migrations** para versionar a estrutura do banco;
- **Rotas REST** para disponibilizar as funcionalidades em JSON;
- **Middleware JWT** para proteger os recursos de cada usuário;
- **Testes automatizados** para validar autenticação, autorização e cálculos financeiros.

Não são utilizados DTOs, repositories ou camadas de casos de uso. Como este projeto é uma API, a camada de apresentação do MVC é formada pelas respostas JSON dos Controllers.

## Tecnologias

- PHP 8.3 ou superior;
- Laravel 13;
- Eloquent ORM;
- MySQL 8.4 para desenvolvimento, testes e entrega;
- PostgreSQL também compatível com as migrations;
- `firebase/php-jwt` para criação e validação dos tokens;
- Pest para testes automatizados;
- PHPStan/Larastan para análise estática;
- Laravel Pint para padronização do código;
- Docker Compose opcional para os bancos de dados.

## Modelo de dados

O sistema possui cinco entidades principais:

```text
User
├── possui várias Accounts
│   └── possui várias Transactions
│       └── pertence a uma Category
├── possui várias Categories
└── possui várias Goals
    └── recebe contribuições de Transactions
```

Relacionamentos:

- um usuário possui várias contas;
- um usuário possui várias categorias;
- um usuário possui várias metas;
- uma conta possui várias transações;
- uma transação pertence a uma conta e a uma categoria;
- transações e metas possuem uma relação muitos-para-muitos;
- a tabela intermediária `goal_transaction` registra o valor destinado à meta.

## Requisitos funcionais

### RF01 — Cadastro de usuário

O sistema deve permitir o cadastro com nome, e-mail e senha. O e-mail deve ser único e a senha deve possuir pelo menos oito caracteres.

Ao concluir o cadastro, o sistema cria automaticamente categorias iniciais:

- receitas: Salário e Investimentos;
- despesas: Alimentação, Moradia, Transporte, Lazer e Saúde.

### RF02 — Autenticação

O usuário deve poder entrar com e-mail e senha. Quando as credenciais estiverem corretas, a API devolve um JWT com o usuário autenticado e o tempo de expiração.

### RF03 — Gerenciamento do token

O usuário autenticado deve poder:

- consultar seus próprios dados;
- renovar um token ainda válido;
- encerrar o acesso removendo o token no front-end.

### RF04 — Gerenciamento de contas

O usuário deve poder listar, cadastrar, consultar, alterar e excluir suas contas financeiras.

Tipos aceitos:

- `checking`: conta bancária;
- `savings`: poupança;
- `wallet`: carteira.

### RF05 — Gerenciamento de categorias

O usuário deve poder administrar categorias próprias de receita e despesa.

Tipos aceitos:

- `income`: receita;
- `expense`: despesa.

### RF06 — Gerenciamento de transações

O usuário deve poder cadastrar, consultar, alterar e excluir receitas e despesas. Também deve ser possível filtrar as transações por:

- conta;
- tipo;
- data exata;
- data inicial e final.

### RF07 — Atualização automática do saldo

Ao criar, alterar ou excluir uma transação, o sistema deve recalcular o saldo da conta envolvida.

### RF08 — Gerenciamento de metas

O usuário deve poder listar, cadastrar, consultar, alterar e excluir metas com título, valor objetivo e prazo opcional.

### RF09 — Contribuições para metas

O usuário deve poder associar uma transação a uma meta, informando quanto do valor da transação foi destinado a ela. Também deve ser possível atualizar ou remover essa associação.

### RF10 — Dashboard

O sistema deve fornecer um resumo com:

- saldo total das contas;
- total de receitas;
- total de despesas;
- contas do usuário;
- metas e seus progressos.

## Regras de negócio

### Autenticação e autorização

- RN01: todas as rotas financeiras exigem um JWT válido no cabeçalho `Authorization`;
- RN02: um usuário só pode acessar contas, categorias, transações e metas que lhe pertencem;
- RN03: um ID de outro usuário é tratado como recurso não encontrado, evitando expor sua existência;
- RN04: a senha é armazenada somente como hash;
- RN05: o JWT expira após o período definido por `JWT_TTL`, cujo padrão é 120 minutos;
- RN06: login e cadastro são limitados a dez tentativas por minuto;
- RN07: o logout é stateless: o front-end deve descartar o token, pois a API não mantém sessão nem blacklist.

### Contas e categorias

- RN08: o saldo inicial de uma conta não pode ser negativo;
- RN09: o saldo não pode ser alterado diretamente na edição da conta;
- RN10: o nome de uma categoria é único por usuário e tipo;
- RN11: uma categoria utilizada por transações não pode ser excluída;
- RN12: uma categoria utilizada não pode mudar de receita para despesa, ou vice-versa.

### Transações e saldo

- RN13: o valor da transação deve ser maior que zero;
- RN14: receitas aumentam o saldo da conta;
- RN15: despesas diminuem o saldo da conta;
- RN16: uma despesa não pode deixar a conta com saldo negativo;
- RN17: categoria e transação devem possuir o mesmo tipo;
- RN18: conta e categoria devem pertencer ao usuário autenticado;
- RN19: ao editar uma transação, o efeito anterior é revertido antes da aplicação dos novos dados;
- RN20: ao excluir uma transação, seu efeito é revertido no saldo;
- RN21: se a transação mudar de conta, a conta anterior é corrigida e o novo efeito é aplicado na conta de destino;
- RN22: uma transação não pode ser reduzida para um valor menor que o total já destinado às metas;
- RN23: alterações de saldo são executadas em transações de banco e utilizam bloqueio da conta para evitar concorrência.

### Metas e contribuições

- RN24: o valor objetivo da meta deve ser maior que zero;
- RN25: o objetivo não pode ser alterado para um valor menor que o total já contribuído;
- RN26: uma contribuição deve ser maior que zero;
- RN27: a soma das contribuições de uma transação não pode superar o valor da própria transação;
- RN28: a soma das contribuições de uma meta não pode superar seu objetivo;
- RN29: a transação usada na contribuição deve pertencer ao usuário da meta;
- RN30: o valor atual, o percentual e a conclusão da meta são calculados pelas contribuições, evitando valores duplicados no banco.

## Estrutura principal

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── AccountController.php
│   │   ├── AuthController.php
│   │   ├── CategoryController.php
│   │   ├── DashboardController.php
│   │   ├── GoalController.php
│   │   └── TransactionController.php
│   └── Middleware/
│       └── AuthenticateJwt.php
├── Models/
│   ├── Account.php
│   ├── Category.php
│   ├── Goal.php
│   ├── Transaction.php
│   └── User.php
└── Services/
    └── JwtService.php

database/
├── migrations/
└── seeders/

routes/
└── api.php

tests/
├── Feature/
└── Unit/
```

## Pré-requisitos

- PHP 8.3 ou superior com `pdo_pgsql` para PostgreSQL ou `pdo_mysql` para MySQL;
- Composer;
- PostgreSQL ou MySQL;
- Git;
- Docker Desktop apenas se os bancos forem executados pelos containers fornecidos.

Confira os drivers habilitados no PHP:

```bash
php -m
```

## Instalação

### 1. Instalar as dependências

```bash
composer install
```

### 2. Criar o arquivo de ambiente

Linux/macOS:

```bash
cp .env.example .env
```

PowerShell:

```powershell
Copy-Item .env.example .env
```

### 3. Gerar as chaves

Gere a chave da aplicação:

```bash
php artisan key:generate
```

Gere uma chave JWT com pelo menos 32 caracteres:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Copie o resultado para o `.env`:

```env
JWT_SECRET=CHAVE_GERADA
JWT_TTL=120
```

Nunca envie o arquivo `.env` para o repositório.

## Configuração do banco

### MySQL (configuração principal)

Configuração padrão de desenvolvimento:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_financeiro
DB_USERNAME=mysql
DB_PASSWORD=sua_senha
```

O MySQL Workbench é apenas a interface de administração. Instale também o MySQL Server 8.4 e confirme que o serviço está em execução. Depois, crie dois bancos pelo Workbench:

```sql
CREATE DATABASE sistema_financeiro
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE DATABASE sistema_financeiro_testing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Para iniciar o MySQL configurado no perfil alternativo do Docker Compose:

```bash
docker compose --profile mysql up -d mysql
```

### PostgreSQL (alternativo)

Para utilizar PostgreSQL, altere somente a configuração de ambiente:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sistema_financeiro
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

Para iniciar apenas o PostgreSQL pelo Docker:

```bash
docker compose up -d postgres
```

As migrations utilizam recursos comuns aos dois bancos, permitindo a troca sem alterações no código da aplicação.

## Preparação da aplicação

Execute as migrations:

```bash
php artisan migrate
```

Para inserir o usuário acadêmico e suas categorias:

```bash
php artisan db:seed
```

Credenciais geradas pelo seeder apenas para desenvolvimento:

```text
E-mail: test@example.com
Senha: password
```

Também é possível executar migrations e seeder juntos:

```bash
php artisan migrate --seed
```

## Executando a API

Inicie o servidor:

```bash
php artisan serve
```

A API ficará disponível em:

```text
http://127.0.0.1:8000
```

Teste o estado básico da aplicação:

```text
GET http://127.0.0.1:8000/
```

Resposta esperada:

```json
{
  "application": "Sistema Financeiro",
  "status": "online"
}
```

## Autenticação JWT

### Cadastro

```http
POST /api/auth/register
Content-Type: application/json
```

```json
{
  "name": "Maria Silva",
  "email": "maria@example.com",
  "password": "senha-segura",
  "password_confirmation": "senha-segura"
}
```

### Login

```http
POST /api/auth/login
Content-Type: application/json
```

```json
{
  "email": "maria@example.com",
  "password": "senha-segura"
}
```

Resposta resumida:

```json
{
  "access_token": "TOKEN_JWT",
  "token_type": "Bearer",
  "expires_in": 7200,
  "user": {
    "id": 1,
    "name": "Maria Silva",
    "email": "maria@example.com"
  }
}
```

Nas rotas protegidas, envie:

```http
Authorization: Bearer TOKEN_JWT
Accept: application/json
```

## Endpoints

### Autenticação

| Método | Endpoint | Protegido | Finalidade |
|---|---|---:|---|
| POST | `/api/auth/register` | Não | Cadastrar usuário |
| POST | `/api/auth/login` | Não | Autenticar e gerar JWT |
| GET | `/api/auth/me` | Sim | Consultar usuário autenticado |
| POST | `/api/auth/refresh` | Sim | Renovar JWT válido |
| POST | `/api/auth/logout` | Sim | Orientar remoção do token |

### Contas

| Método | Endpoint | Finalidade |
|---|---|---|
| GET | `/api/accounts` | Listar contas |
| POST | `/api/accounts` | Criar conta |
| GET | `/api/accounts/{id}` | Consultar conta |
| PUT/PATCH | `/api/accounts/{id}` | Alterar nome e tipo |
| DELETE | `/api/accounts/{id}` | Excluir conta |

Exemplo de criação:

```json
{
  "name": "Conta principal",
  "type": "checking",
  "balance": 1000.00
}
```

### Categorias

| Método | Endpoint | Finalidade |
|---|---|---|
| GET | `/api/categories` | Listar categorias |
| POST | `/api/categories` | Criar categoria |
| GET | `/api/categories/{id}` | Consultar categoria |
| PUT/PATCH | `/api/categories/{id}` | Alterar categoria |
| DELETE | `/api/categories/{id}` | Excluir categoria sem uso |

Exemplo:

```json
{
  "name": "Freelance",
  "type": "income"
}
```

### Transações

| Método | Endpoint | Finalidade |
|---|---|---|
| GET | `/api/transactions` | Listar e filtrar transações |
| POST | `/api/transactions` | Criar receita ou despesa |
| GET | `/api/transactions/{id}` | Consultar transação |
| PUT/PATCH | `/api/transactions/{id}` | Alterar e recalcular saldo |
| DELETE | `/api/transactions/{id}` | Excluir e reverter saldo |

Exemplo de despesa:

```json
{
  "account_id": 1,
  "category_id": 3,
  "description": "Compras do mês",
  "amount": 150.50,
  "type": "expense",
  "transaction_date": "2026-09-20"
}
```

Filtros disponíveis:

```text
GET /api/transactions?account_id=1
GET /api/transactions?type=expense
GET /api/transactions?date=2026-09-20
GET /api/transactions?start_date=2026-09-01&end_date=2026-09-30
```

### Metas

| Método | Endpoint | Finalidade |
|---|---|---|
| GET | `/api/goals` | Listar metas |
| POST | `/api/goals` | Criar meta |
| GET | `/api/goals/{id}` | Consultar meta e contribuições |
| PUT/PATCH | `/api/goals/{id}` | Alterar meta |
| DELETE | `/api/goals/{id}` | Excluir meta |

Exemplo:

```json
{
  "title": "Reserva de emergência",
  "target_amount": 5000.00,
  "deadline": "2027-12-31"
}
```

A resposta inclui campos calculados:

- `current_amount`: soma das contribuições;
- `completed`: informa se o objetivo foi alcançado;
- `progress_percentage`: percentual de conclusão limitado a 100%.

### Contribuições

Criar ou atualizar uma contribuição:

```http
POST /api/goals/{goal_id}/contributions
```

```json
{
  "transaction_id": 1,
  "contributed_amount": 500.00
}
```

Remover o vínculo entre meta e transação:

```http
DELETE /api/goals/{goal_id}/contributions/{transaction_id}
```

### Dashboard

```http
GET /api/dashboard
```

Retorna `total_balance`, `total_income`, `total_expense`, contas e metas do usuário.

## Fluxo sugerido para teste manual

As funcionalidades podem ser testadas no Postman, Insomnia ou outra ferramenta HTTP:

1. execute `POST /api/auth/register`;
2. copie o campo `access_token` da resposta;
3. configure `Authorization: Bearer TOKEN` nas próximas requisições;
4. execute `GET /api/categories` e identifique os IDs criados automaticamente;
5. crie uma conta com `POST /api/accounts`;
6. crie uma receita ou despesa com `POST /api/transactions`;
7. confira o novo saldo com `GET /api/accounts/{id}`;
8. crie uma meta com `POST /api/goals`;
9. vincule uma transação usando o endpoint de contribuições;
10. consulte `GET /api/dashboard` para visualizar o resumo.

## Códigos HTTP mais utilizados

| Código | Significado |
|---:|---|
| 200 | Consulta ou alteração realizada |
| 201 | Recurso criado |
| 204 | Exclusão realizada sem conteúdo de resposta |
| 401 | Token ausente, inválido, expirado ou credenciais incorretas |
| 404 | Recurso inexistente ou pertencente a outro usuário |
| 409 | Conflito com o estado atual do recurso |
| 422 | Falha de validação ou regra financeira |

## Testes automatizados

Os testes usam o banco MySQL isolado `sistema_financeiro_testing`. Nunca utilize aqui o banco normal `sistema_financeiro`, pois a suíte recria as tabelas.

Crie a configuração local de testes no PowerShell:

```powershell
Copy-Item .env.testing.example .env.testing
```

Preencha `DB_USERNAME` e `DB_PASSWORD` no `.env.testing` com as credenciais da sua instalação. Em seguida, gere uma chave exclusiva para esse ambiente:

```bash
php artisan key:generate --env=testing
```

O `phpunit.xml` contém somente opções seguras da suíte; credenciais ficam no `.env.testing`, que é ignorado pelo Git.

Execute:

```bash
php artisan test
```

Os testes utilizam `RefreshDatabase` e recriam as tabelas. Nunca configure a suíte para utilizar um banco com dados importantes.

Verificações de qualidade:

```bash
composer lint:check
composer types:check
php artisan test
```

Cobertura funcional atual:

- cadastro, login e validação JWT;
- bloqueio de rotas sem token;
- isolamento de contas por usuário;
- criação, alteração e exclusão de transações;
- atualização e reversão do saldo;
- validação de saldo insuficiente;
- compatibilidade entre categoria e transação;
- cálculo do progresso das metas;
- proteção dos limites de contribuição.

## Compatibilidade com PostgreSQL

Para repetir a suíte no PostgreSQL, copie o arquivo de testes e troque apenas `DB_CONNECTION`, `DB_PORT`, `DB_USERNAME` e `DB_PASSWORD`. Mantenha sempre `DB_DATABASE=sistema_financeiro_testing`.

## Observações de segurança

- não compartilhe `APP_KEY`, `JWT_SECRET` ou senhas do banco;
- utilize HTTPS quando a API for publicada;
- mantenha o token em um local seguro no front-end;
- as credenciais do seeder são somente para desenvolvimento;
- configure `FRONTEND_URL` no `.env` para limitar a origem permitida pelo CORS;
- use credenciais diferentes das apresentadas nos exemplos em produção.
