<?php

namespace App\Exceptions\Wallets;

use Exception;

class InsufficientFundsException extends Exception
{
    public function __construct(string $message = "Insufficient funds to perform this operation.", int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
