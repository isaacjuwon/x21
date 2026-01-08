<?php

namespace App\Exceptions\Loans;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function __construct(
        string $message = "Insufficient wallet balance to make loan payment.",
    ) {
        parent::__construct($message);
    }
}
