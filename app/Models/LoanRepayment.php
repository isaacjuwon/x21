<?php

namespace App\Models;

use Database\Factories\LoanRepaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    /** @use HasFactory<LoanRepaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'amount',
        'transaction_id',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
