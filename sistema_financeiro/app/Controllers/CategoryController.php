<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Category;

/** Gerencia as categorias e impede alterações que quebrariam o histórico financeiro. */
final class CategoryController extends Controller
{
    public function index(Request $r): never
    {
        Response::json(Category::allForUser($this->userId($r)));
    }
    public function store(Request $r): never
    {
        $d = $this->validated($r);
        $this->unique($r, $d);
        Response::json(Category::create($this->userId($r), $d), 201);
    }
    public function show(Request $r): never
    {
        $c = Category::findForUser($this->id($r, 'category'), $this->userId($r)) ?? $this->notFound();
        Response::json($c);
    }
    public function update(Request $r): never
    {
        $id = $this->id($r, 'category');
        $c = Category::findForUser($id, $this->userId($r)) ?? $this->notFound();
        $d = $this->validated($r);
        $this->unique($r, $d, $id);
        if ($c['type'] !== $d['type'] && Category::hasTransactions($id)) {
            throw new HttpException(409, 'O tipo de uma categoria já utilizada não pode ser alterado.');
        }
        Response::json(Category::update($id, $this->userId($r), $d));
    }
    public function destroy(Request $r): never
    {
        $id = $this->id($r, 'category');
        Category::findForUser($id, $this->userId($r)) ?? $this->notFound();
        if (Category::hasTransactions($id)) {
            throw new HttpException(409, 'A categoria possui transações e não pode ser excluída.');
        }
        Category::delete($id, $this->userId($r));
        Response::json(null, 204);
    }
    private function validated(Request $r): array
    {
        $d = Validator::validate($r->all(), ['name' => ['required', 'string', 'min:3', 'max:100'], 'type' => ['required', 'in:' . implode(',', Category::TYPES)], 'status' => ['sometimes', 'in:' . implode(',', Category::STATUSES)]]);
        $d['status'] = $d['status'] ?? 'active';
        return $d;
    }
    private function unique(Request $r, array $d, ?int $ignore = null): void
    {
        if (Category::existsNameType($this->userId($r), $d['name'], $d['type'], $ignore)) {
            throw new ValidationException(['name' => ['Já existe uma categoria com este nome e tipo.']]);
        }
    }
}
