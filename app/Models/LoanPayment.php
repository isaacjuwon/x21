<?php

namespace App\Models;

use App\Enums\LoanPaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        "loan_id",
        "amount",
        "payment_type",
        "payment_date",
        "due_date",
        "principal_amount",
        "interest_amount",
        "balance_after",
        "wallet_transaction_id",
    ];

    protected $casts = [
        "amount" => "decimal:2",
        "principal_amount" => "decimal:2",
        "interest_amount" => "decimal:2",
        "balance_after" => "decimal:2",
        "payment_type" => LoanPaymentType::class,
        "payment_date" => "datetime",
        "due_date" => "date",
    ];

    /**
     * Get the loan that owns the payment
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Get the associated wallet transaction
     */
    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    /**
     * Get formatted payment amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get formatted principal amount
     */
    public function getFormattedPrincipalAttribute(): string
    {
        return number_format($this->principal_amount, 2);
    }

    /**
     * Get formatted interest amount
     */
    public function getFormattedInterestAttribute(): string
    {
        return number_format($this->interest_amount, 2);
    }
}
