<?php

namespace App\Models;

use App\Enums\Transaction\Status;
use App\Enums\Transaction\Type;
use App\Models\Builders\TransactionBuilder;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'amount',
        'description',
        'recipient',
        'status',
        'type',
        'reference',
        'response',
        'meta',
        'payment_method',
        'archived',
        'user_id',
        'transactable_type',
        'transactable_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'type' => Type::class,
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: User::class,
            foreignKey: 'user_id',
        );
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => Status::Failed]);
    }

    public function markAsSuccess(): void
    {
        $this->update(['status' => Status::Success]);
    }

    public function markAsPending(): void
    {
        $this->update(['status' => Status::Pending]);
    }

    public function newEloquentBuilder($query): BuilderContract
    {
        return new TransactionBuilder(
            query: $query
        );
    }

    public function generateCheckInCode(): string
    {
        return Str::random(
            length: 6,
        );
    }

    public function transactable(): MorphTo
    {
        return $this->morphTo();
    }
}
