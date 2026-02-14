<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'loan_level_id',
        'amount',
        'interest_rate',
        'installment_months',
        'monthly_payment',
        'total_repayment',
        'amount_paid',
        'balance_remaining',
        'shares_required',
        'shares_value_at_application',
        'status',
        'applied_at',
        'approved_at',
        'disbursed_at',
        'fully_paid_at',
        'next_payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'total_repayment' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_remaining' => 'decimal:2',
        'shares_value_at_application' => 'decimal:2',
        'installment_months' => 'integer',
        'shares_required' => 'integer',

        'status' => LoanStatus::class,
        'applied_at' => 'datetime',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'fully_paid_at' => 'datetime',
        'next_payment_date' => 'date',
    ];

    /**
     * Get the user that owns the loan
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the loan level
     */
    public function loanLevel(): BelongsTo
    {
        return $this->belongsTo(LoanLevel::class);
    }

    /**
     * Get loan payments
     */
    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    /**
     * Scope to get active loans
     */
    public function scopeActive($query)
    {
        return $query->where('status', LoanStatus::ACTIVE);
    }

    /**
     * Scope to get fully paid loans
     */
    public function scopeFullyPaid($query)
    {
        return $query->where('status', LoanStatus::FULLYPAID);
    }

    /**
     * Scope to get loans for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_repayment == 0) {
            return 0;
        }

        return round(($this->amount_paid / $this->total_repayment) * 100, 2);
    }

    /**
     * Check if loan is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if (! $this->next_payment_date || $this->status !== 'active') {
            return false;
        }

        return now()->gt($this->next_payment_date);
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->status?->getColor() ?? 'secondary';
    }

    /**
     * Check if loan has pending payment
     */
    public function hasPendingPayment(): bool
    {
        return $this->status === LoanStatus::ACTIVE && $this->balance_remaining > 0;
    }

    /**
     * Update loan balance after payment
     */
    public function updateBalance(float $paymentAmount): void
    {
        $this->amount_paid += $paymentAmount;
        $this->balance_remaining -= $paymentAmount;

        if ($this->balance_remaining <= 0) {
            $this->balance_remaining = 0;
            $this->markAsPaid();
        } else {
            $this->syncNextPaymentDate();
        }

        $this->save();
    }

    /**
     * Sync next payment date with fixed schedule
     */
    public function syncNextPaymentDate(): void
    {
        $calculator = new class
        {
            use \App\Concerns\CalculatesLoanEligibility;
        };

        $this->next_payment_date = $calculator->calculateNextDueDate($this);
    }

    /**
     * Mark loan as fully paid
     */
    public function markAsPaid(): void
    {
        $this->status = LoanStatus::FULLYPAID;
        $this->fully_paid_at = now();
        $this->next_payment_date = null;
        $this->save();
    }
}
