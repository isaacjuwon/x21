<?php

namespace App\Exceptions\Loans;

use Exception;

class ActiveLoanExistsException extends Exception
{
    public function __construct(
        string $message = "You already have an active loan. Please complete your current loan before applying for a new one.",
    ) {
        parent::__construct($message);
    }
}
