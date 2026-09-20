<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $account_id
 * @property int $category_id
 * @property string $description
 * @property string $amount
 * @property string $type
 */
class Transaction extends Model
{
    public const TYPES = ['income', 'expense'];

    protected $fillable = [
        'account_id',
        'category_id',
        'description',
        'amount',
        'type',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date:Y-m-d',
        ];
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsToMany<Goal, $this> */
    public function goals(): BelongsToMany
    {
        return $this->belongsToMany(Goal::class)
            ->withPivot('contributed_amount')
            ->withTimestamps();
    }
}
