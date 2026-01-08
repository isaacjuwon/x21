<?php

namespace App\Events\Loans;

use App\Models\Loan;
use App\Models\User;

class LoanFullyPaid extends LoanEvent
{
    public function __construct(Loan $loan, User $user)
    {
        parent::__construct($loan, $user);
    }
}
