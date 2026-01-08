<?php

namespace App\Concerns;

use App\Models\Loan;
use App\Models\LoanLevel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasLoans
{
    /**
     * Get all loans for the user
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get the user's loan level
     */
    public function loanLevel(): BelongsTo
    {
        return $this->belongsTo(LoanLevel::class);
    }

    /**
     * Check if user has an active loan
     */
    public function hasActiveLoan(): bool
    {
        return $this->loans()->active()->exists();
    }

    /**
     * Get the user's current active loan
     */
    public function activeLoan(): ?Loan
    {
        return $this->loans()->active()->first();
    }

    /**
     * Check if user can apply for a loan
     */
    public function canApplyForLoan(float $amount = 0): bool
    {
        // User must not have an active loan
        if ($this->hasActiveLoan()) {
            return false;
        }

        // User must have a loan level
        if (! $this->loan_level_id) {
            return false;
        }

        // User must have required shares (based on loan_to_shares_ratio setting)
        if (! $this->hasRequiredShares()) {
            return false;
        }

        // If amount is specified, check if it's within eligible amount
        if ($amount > 0) {
            $eligibleAmount = $this->getLoanEligibilityAmount();

            return $amount <= $eligibleAmount;
        }

        return true;
    }

    /**
     * Get the maximum loan amount user is eligible for
     * Based on loan_to_shares_ratio setting and loan level maximum
     */
    public function getLoanEligibilityAmount(): float
    {
        if (! $this->loan_level_id) {
            return 0;
        }

        $loanSettings = app(\App\Settings\LoanSettings::class);
        $sharesValue = $this->getSharesValue();
        $eligibleBasedOnShares = $sharesValue * $loanSettings->loan_to_shares_ratio;

        $loanLevel = $this->loanLevel;
        $maxForLevel = $loanLevel ? $loanLevel->maximum_loan_amount : 0;

        // Return the minimum of the two
        return min($eligibleBasedOnShares, $maxForLevel);
    }

    /**
     * Calculate the total value of user's shares
     * Multiplies quantity by share price from settings
     */
    public function getSharesValue(): float
    {
        $shares = $this->shares()->where('currency', 'SHARE')->first();

        if (!$shares) {
            return 0;
        }

        $shareSettings = app(\App\Settings\ShareSettings::class);
        
        return (float) $shares->quantity * $shareSettings->share_price;
    }

    /**
     * Check if user has required shares (based on loan_to_shares_ratio setting)
     * Assuming minimum share value needed is based on loan level
     */
    public function hasRequiredShares(): bool
    {
        $sharesValue = $this->getSharesValue();

        // User needs at least some shares to be eligible
        return $sharesValue > 0;
    }
}
