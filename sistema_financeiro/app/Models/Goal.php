<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $target_amount
 * @property string $current_amount
 * @property bool $completed
 * @property float $progress_percentage
 */
class Goal extends Model
{
    protected $fillable = ['user_id', 'title', 'target_amount', 'deadline'];

    protected $appends = ['current_amount', 'completed', 'progress_percentage'];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'deadline' => 'date:Y-m-d',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<Transaction, $this> */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class)
            ->withPivot('contributed_amount')
            ->withTimestamps();
    }

    public function getCurrentAmountAttribute(): string
    {
        return number_format((float) $this->transactions()->sum('contributed_amount'), 2, '.', '');
    }

    public function getCompletedAttribute(): bool
    {
        return (float) $this->current_amount >= (float) $this->target_amount;
    }

    public function getProgressPercentageAttribute(): float
    {
        if ((float) $this->target_amount === 0.0) {
            return 0;
        }

        return round(min(100, ((float) $this->current_amount / (float) $this->target_amount) * 100), 2);
    }
}
