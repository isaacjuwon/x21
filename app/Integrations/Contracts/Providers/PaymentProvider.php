<?php

namespace App\Integrations\Contracts\Providers;

use App\Integrations\Paystack\Entities\InitializePayment;
use App\Integrations\Paystack\Entities\PaymentResponse;

interface PaymentProvider
{
    public function initializePayment(InitializePayment $entity): PaymentResponse;

    public function verifyPayment(string $reference): PaymentResponse;
}
