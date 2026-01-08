<?php

namespace App\Exceptions\Loans;

use Exception;

class InsufficientSharesForLoanException extends Exception
{
    public function __construct(
        string $message = "Insufficient shares to qualify for loan. You need at least 15% shares value for the requested amount.",
    ) {
        parent::__construct($message);
    }
}
