<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Goal;
use App\Models\Preference;

/** Salva preferências visuais do usuário, como tema e ordem dos cards de metas. */
final class PreferenceController extends Controller
{
    public function show(Request $r): never
    {
        Response::json(Preference::forUser($this->userId($r)));
    }
    public function update(Request $r): never
    {
        $d = Validator::validate($r->all(), ['theme' => ['sometimes', 'in:' . implode(',', Preference::THEMES)], 'goal_order' => ['sometimes', 'array']]);
        if (isset($d['goal_order'])) {
            $valid = array_map(fn($g) => (int)$g['id'], Goal::allForUser($this->userId($r)));
            foreach ($d['goal_order'] as $id) {
                if (!in_array((int)$id, $valid, true)) {
                    throw new ValidationException(['goal_order' => ['A ordem contém uma meta inválida.']]);
                }
            }
            $d['goal_order'] = array_map('intval', $d['goal_order']);
        }
        Response::json(Preference::update($this->userId($r), $d));
    }
}
