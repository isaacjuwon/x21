<?php

namespace App\Events\Loans;

use App\Models\Loan;
use App\Models\User;

class LoanDisbursed extends LoanEvent
{
    public function __construct(
        Loan $loan,
        User $user,
        public float $amount,
    ) {
        parent::__construct($loan, $user);
    }
}
