<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->categories()->orderBy('type')->orderBy('name')->get(),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $category = $request->user()->categories()->create($data);

        return response()->json($category, 201);
    }

    public function show(Request $request, int $category): JsonResponse
    {
        return response()->json($this->findCategory($request, $category));
    }

    public function update(Request $request, int $category): JsonResponse
    {
        $category = $this->findCategory($request, $category);
        $data = $this->validated($request, $category->id);

        if ($category->type !== $data['type'] && $category->transactions()->exists()) {
            return response()->json([
                'message' => 'O tipo de uma categoria já utilizada não pode ser alterado.',
            ], 409);
        }

        $category->update($data);

        return response()->json($category);
    }

    public function destroy(Request $request, int $category): JsonResponse
    {
        $category = $this->findCategory($request, $category);

        if ($category->transactions()->exists()) {
            return response()->json([
                'message' => 'A categoria possui transações e não pode ser excluída.',
            ], 409);
        }

        $category->delete();

        return response()->json(null, 204);
    }

    /** @return array{name: string, type: string} */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'min:3', 'max:100',
                Rule::unique('categories')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->where('type', $request->input('type')))->ignore($ignoreId),
            ],
            'type' => ['required', Rule::in(Category::TYPES)],
        ]);
    }

    private function findCategory(Request $request, int $id): Category
    {
        return $request->user()->categories()->findOrFail($id);
    }
}
