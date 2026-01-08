<?php

namespace App\Events\Loans;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class LoanEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Loan $loan,
        public User $user,
    ) {
    }
}
