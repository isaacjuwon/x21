<?php

namespace App\Exceptions\Loans;

use App\Loans\Specifications\LoanSpecification;

class LoanIneligibleException extends \RuntimeException
{
    public function __construct(private readonly LoanSpecification $failingSpecification)
    {
        parent::__construct($failingSpecification->failureReason());
    }

    public function getFailingSpecification(): LoanSpecification
    {
        return $this->failingSpecification;
    }
}
