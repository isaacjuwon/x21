<?php

namespace App\Exceptions\Shares;

use Exception;

class InvalidShareQuantityException extends Exception
{
    public function __construct(string $message = "Invalid share quantity")
    {
        parent::__construct($message);
    }
}
