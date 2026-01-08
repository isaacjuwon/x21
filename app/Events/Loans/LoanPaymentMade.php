<?php

namespace App\Events\Loans;

use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanPaymentMade
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Loan $loan,
        public LoanPayment $payment,
        public User $user,
    ) {
    }
}
