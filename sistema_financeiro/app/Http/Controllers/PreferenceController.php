<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PreferenceController extends Controller
{
    /** Retorna as preferências, criando os padrões na primeira utilização. */
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->preference($request));
    }

    /** Persiste tema e/ou ordem visual das metas do usuário. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'theme' => ['sometimes', Rule::in(UserPreference::THEMES)],
            'goal_order' => ['sometimes', 'array'],
            'goal_order.*' => ['integer', 'distinct'],
        ]);

        if (isset($data['goal_order'])) {
            $validIds = $request->user()->goals()
                ->whereIn('id', $data['goal_order'])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $data['goal_order'] = array_values(array_filter(
                $data['goal_order'],
                fn ($id) => in_array((int) $id, $validIds, true),
            ));
        }

        $preference = $this->preference($request);
        $preference->update($data);

        return response()->json($preference->fresh());
    }

    private function preference(Request $request): UserPreference
    {
        return $request->user()->preference()->firstOrCreate([], [
            'theme' => 'light',
            'goal_order' => [],
        ]);
    }
}
