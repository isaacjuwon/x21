<?php

namespace App\Exceptions\Loans;

use Exception;

class LoanLimitExceededException extends Exception
{
    public function __construct(
        string $message = "Requested loan amount exceeds your loan level limit or eligible amount.",
    ) {
        parent::__construct($message);
    }
}
