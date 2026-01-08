<?php

namespace App\Models\Builders;

use App\Enums\Loan\Status;
use Illuminate\Database\Eloquent\Builder;

class LoanBuilder extends Builder
{
    /**
     * Scope a query to only include active loans.
     */
    public function active(): LoanBuilder
    {
        return $this->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive loans.
     */
    public function inactive(): LoanBuilder
    {
        return $this->where('is_active', false);
    }

    /**
     * Scope a query to only include loans with a specific status.
     */
    public function status(Status $status): LoanBuilder
    {
        return $this->where('status', $status);
    }

    /**
     * Scope a query to only include pending loans.
     */
    public function pending(): LoanBuilder
    {
        return $this->where('status', operator: Status::PENDING);
    }

    /**
     * Scope a query to only include under review loans.
     */
    public function underReview(): LoanBuilder
    {
        return $this->where('status', Status::UNDER_REVIEW);
    }

    /**
     * Scope a query to only include approved loans.
     */
    public function approved(): LoanBuilder
    {
        return $this->where('status', Status::APPROVED);
    }

    /**
     * Scope a query to only include rejected loans.
     */
    public function rejected(): LoanBuilder
    {
        return $this->where('status', Status::REJECTED);
    }

    /**
     * Scope a query to only include disbursed loans.
     */
    public function disbursed(): LoanBuilder
    {
        return $this->where('status', Status::DISBURSED);
    }

    /**
     * Scope a query to only include completed loans.
     */
    public function completed(): LoanBuilder
    {
        return $this->where('status', Status::COMPLETED);
    }

    /**
     * Scope a query to only include defaulted loans.
     */
    public function defaulted(): LoanBuilder
    {
        return $this->where('status', Status::DEFAULTED);
    }

    /**
     * Scope a query to only include cancelled loans.
     */
    public function cancelled(): LoanBuilder
    {
        return $this->where('status', Status::CANCELLED);
    }

    /**
     * Scope a query to only include overdue loans.
     */
    public function overdue(): LoanBuilder
    {
        return $this->where('expected_end_date', '<', now())
            ->where('remaining_balance', '>', 0);
    }

    /**
     * Scope a query to only include loans with remaining balance.
     */
    public function withBalance(): LoanBuilder
    {
        return $this->where('remaining_balance', '>', 0);
    }

    /**
     * Scope a query to only include loans without remaining balance.
     */
    public function withoutBalance(): LoanBuilder
    {
        return $this->where('remaining_balance', 0);
    }

    /**
     * Scope a query to only include loans for a specific user.
     */
    public function forUser($userId): LoanBuilder
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Scope a query to only include loans with payments due before a specific date.
     */
    public function dueBefore($date): LoanBuilder
    {
        return $this->where('expected_end_date', '<', $date);
    }

    /**
     * Scope a query to only include loans with payments due after a specific date.
     */
    public function dueAfter($date): LoanBuilder
    {
        return $this->where('expected_end_date', '>', $date);
    }
}