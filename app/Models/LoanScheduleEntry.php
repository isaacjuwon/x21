<?php

namespace App\Models;

use App\Enums\Loans\LoanScheduleEntryStatus;
use Database\Factories\LoanScheduleEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanScheduleEntry extends Model
{
    /** @use HasFactory<LoanScheduleEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'instalment_number',
        'due_date',
        'instalment_amount',
        'principal_component',
        'interest_component',
        'outstanding_balance',
        'status',
        'remaining_amount',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanScheduleEntryStatus::class,
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
