<?php

namespace App\Events\Loans;

use App\Models\Loan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanSettled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Loan $loan) {}
}
