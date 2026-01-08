<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "slug",
        "maximum_loan_amount",
        "installment_period_months",
        "interest_rate",
        "repayments_required_for_upgrade",
        "is_active",
    ];

    protected $casts = [
        "maximum_loan_amount" => "decimal:2",
        "interest_rate" => "decimal:2",
        "installment_period_months" => "integer",
        "repayments_required_for_upgrade" => "integer",
        "is_active" => "boolean",
    ];

    /**
     * Get users with this loan level
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get loans for this level
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Scope to get only active loan levels
     */
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }

    /**
     * Get formatted maximum loan amount
     */
    public function getFormattedMaximumLoanAmountAttribute(): string
    {
        return number_format($this->maximum_loan_amount, 2);
    }
}
