# Finaxis — Sistema Financeiro em PHP

O Finaxis é um sistema didático de gestão financeira pessoal.

## Funcionalidades

- cadastro, login, renovação e encerramento de sessão com JWT;
- edição do perfil, alteração de senha e exclusão da conta;
- contas financeiras com tipo, status e saldo;
- categorias de receita e despesa com status;
- receitas e despesas com atualização automática do saldo;
- metas, contribuições, progresso e reordenação dos cards;
- Dashboard com filtros, totais, transações, metas e contas;
- tema claro/escuro e preferências por usuário;
- API JSON para o front-end web, Postman e futuro aplicativo mobile.

## Organização do projeto

```text
sistema_financeiro/
├── app/
│   ├── Controllers/   # recebe requisições e aplica as regras
│   ├── Core/          # roteador, banco, JWT, validação e respostas
│   ├── Models/        # consultas SQL e acesso aos dados
│   ├── Views/         # páginas PHP do front-end web
│   ├── bootstrap.php  # inicia a aplicação
│   └── routes.php     # rotas web e da API
├── database/
│   ├── schema.sql     # estrutura completa do MySQL
│   └── install.php    # instalação opcional pelo terminal
├── public/            # CSS, JavaScript, imagens e entrada pública
├── tests/             # testes simples, sem PHPUnit
├── .env.example       # exemplo de configuração
└── index.php          # entrada da aplicação no Apache
```

O fluxo principal é: **rota → controller → model → MySQL → resposta JSON ou view**. As consultas usam PDO e comandos preparados para que os valores enviados pelo usuário não sejam inseridos diretamente no SQL.

## Requisitos

- XAMPP com Apache, MySQL e PHP 8.1 ou superior;
- extensões `pdo_mysql`, `mbstring` e `openssl` habilitadas;
- módulo `mod_rewrite` habilitado no Apache.

Não é necessário instalar Laravel, Composer, Node.js ou npm.

## Instalação com XAMPP

1. Copie a pasta `sistema_financeiro` para `C:\xampp\htdocs\sistema_financeiro`.
2. Inicie **Apache** e **MySQL** no painel do XAMPP.
3. Abra `http://localhost/phpmyadmin`.
4. Use a aba **Importar** e selecione `database/schema.sql`.
5. Copie `.env.example` para `.env` e confira o usuário e a senha do MySQL.
6. Troque `JWT_SECRET` por uma chave aleatória com pelo menos 32 caracteres.
7. Acesse `http://localhost/sistema_financeiro/cadastro`.

Como alternativa ao phpMyAdmin, o banco pode ser preparado no terminal, dentro da pasta do projeto:

```bash
php database/install.php
```

Se a pasta dentro de `htdocs` tiver outro nome, ajuste estas duas linhas no `.env`:

```env
APP_URL=http://localhost/nome_da_pasta
APP_BASE_PATH=/nome_da_pasta
```

## Testes pelo Postman

Use `Content-Type: application/json` e `Accept: application/json`.

Cadastro:

```http
POST http://localhost/sistema_financeiro/api/auth/register

{
  "name": "Usuário de Teste",
  "email": "teste@example.com",
  "password": "teste@123",
  "password_confirmation": "teste@123"
}
```

Login:

```http
POST http://localhost/sistema_financeiro/api/auth/login

{
  "email": "teste@example.com",
  "password": "teste@123"
}
```

Para rotas protegidas, copie o `token` retornado e envie o cabeçalho:

```http
Authorization: Bearer SEU_TOKEN
```

Principais endpoints:

| Método | Endereço | Função |
|---|---|---|
| POST | `/api/auth/register` | cadastrar usuário |
| POST | `/api/auth/login` | entrar |
| POST | `/api/auth/logout` | sair |
| GET/POST | `/api/accounts` | listar/cadastrar contas |
| GET/POST | `/api/categories` | listar/cadastrar categorias |
| GET/POST | `/api/transactions` | listar/cadastrar transações |
| GET/POST | `/api/goals` | listar/cadastrar metas |
| POST | `/api/goals/{id}/contributions` | contribuir para uma meta |
| GET | `/api/dashboard` | obter o resumo financeiro |

Os recursos aceitam também `GET`, `PUT` e `DELETE` em `/api/recurso/{id}`.

## Regras financeiras

- cada usuário acessa somente seus próprios dados;
- contas e categorias inativas continuam no histórico, mas não aparecem em novos cadastros ou filtros ativos;
- uma receita aumenta o saldo e uma despesa diminui o saldo;
- não é permitido criar uma despesa ou contribuição acima do saldo disponível;
- ao editar uma transação, o efeito antigo é estornado antes da aplicação do novo valor;
- ao excluir uma transação, o saldo é estornado;
- a categoria de contribuição de meta é interna e permanece inativa;
- uma contribuição não pode ultrapassar o valor restante da meta;
- excluir uma meta ou contribuição devolve os valores às contas correspondentes;
- categorias já usadas em transações não podem mudar de tipo nem ser excluídas.

## Futuro aplicativo Android

No emulador do Android Studio, `localhost` aponta para o próprio emulador. Para alcançar o XAMPP do computador, a URL base normalmente será:

```text
http://10.0.2.2/sistema_financeiro/api
```

Em um celular físico, use o endereço IP do computador na mesma rede. O aplicativo deverá enviar o JWT no cabeçalho `Authorization: Bearer ...`.

## Executar os testes locais

```bash
php tests/run.php
```

O teste não altera o banco: ele valida componentes independentes, como JWT e validações. Para conferir toda a integração, execute também o fluxo de cadastro, contas, categorias, transações e metas pela interface ou pelo Postman.
