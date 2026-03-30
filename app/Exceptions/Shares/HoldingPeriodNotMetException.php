<?php

namespace App\Exceptions\Shares;

use Illuminate\Support\Carbon;

class HoldingPeriodNotMetException extends \RuntimeException
{
    public function __construct(private readonly Carbon $earliestSellDate)
    {
        parent::__construct();
    }

    public function getEarliestSellDate(): Carbon
    {
        return $this->earliestSellDate;
    }
}
