<?php
// app/Exceptions/Shares/InsufficientSharesException.php

namespace App\Exceptions\Shares;

use Exception;

class InsufficientSharesException extends Exception
{
    public function __construct(
        string $message = "Insufficient shares to complete the transaction",
    ) {
        parent::__construct($message);
    }
}
