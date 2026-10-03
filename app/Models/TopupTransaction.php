<?php

namespace App\Models;

use App\Enums\Topups\TopupTransactionStatus;
use App\Enums\Topups\TopupType;
use Database\Factories\TopupTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class TopupTransaction extends Model
{
    /** @use HasFactory<TopupTransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'plan_type',
        'brand_id',
        'type',
        'amount',
        'recipient',
        'reference',
        'api_reference',
        'status',
        'response_message',
        'meta',
    ];

    protected $casts = [
        'type' => TopupType::class,
        'status' => TopupTransactionStatus::class,
        'amount' => 'decimal:2',
        'meta' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function plan()
    {
        return $this->morphTo();
    }

    /**
     * The wallet Transaction record that debited the user for this topup.
     */
    public function walletTransaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    /**
     * Mark this topup as failed and trigger a wallet reversal via the
     * TransactionFailed event → DispatchWalletReversalListener chain.
     */
    public function fail(string $reason): void
    {
        $this->update(['status' => TopupTransactionStatus::Failed]);

        $this->walletTransaction?->fail($reason);
    }
}
