<?php

namespace App\Models;

use App\Enums\Loans\InterestMethod;
use App\Enums\Loans\LoanStatus;
use Database\Factories\LoanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    /** @use HasFactory<LoanFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'principal_amount',
        'outstanding_balance',
        'interest_rate',
        'repayment_term_months',
        'interest_method',
        'status',
        'disbursed_at',
        'eligibility_checked_at',
        'eligibility_passed',
        'notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'interest_method' => InterestMethod::class,
            'disbursed_at' => 'datetime',
            'eligibility_checked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scheduleEntries(): HasMany
    {
        return $this->hasMany(LoanScheduleEntry::class);
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LoanStatusHistory::class);
    }
}
