<?php

namespace App\Loans\Specifications;

use App\Models\User;

interface LoanSpecification
{
    public function isSatisfiedBy(User $user): bool;

    public function failureReason(): string;
}
