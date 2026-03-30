<?php

namespace App\Models;

use Database\Factories\ShareHoldingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareHolding extends Model
{
    /** @use HasFactory<ShareHoldingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quantity',
        'acquired_at',
    ];

    protected function casts(): array
    {
        return [
            'acquired_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
