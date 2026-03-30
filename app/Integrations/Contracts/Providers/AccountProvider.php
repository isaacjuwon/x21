<?php

namespace App\Integrations\Contracts\Providers;

use App\Integrations\Paystack\Entities\BankAccount;
use App\Integrations\Paystack\Entities\RecipientResponse;

interface AccountProvider
{
    public function createRecipient(BankAccount $entity): RecipientResponse;
}
