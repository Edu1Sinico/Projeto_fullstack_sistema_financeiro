<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    public const THEMES = ['light', 'dark'];

    protected $fillable = ['user_id', 'theme', 'goal_order'];

    protected function casts(): array
    {
        return ['goal_order' => 'array'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
