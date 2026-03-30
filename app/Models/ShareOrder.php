<?php

namespace App\Models;

use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use Database\Factories\ShareOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareOrder extends Model
{
    /** @use HasFactory<ShareOrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'quantity',
        'price_per_share',
        'total_amount',
        'status',
        'hold_transaction_id',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShareOrderStatus::class,
            'type' => ShareOrderType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function holdTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'hold_transaction_id');
    }
}
