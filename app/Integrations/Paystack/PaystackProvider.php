<?php

namespace App\Integrations\Paystack;

use App\Integrations\Contracts\Providers\AccountProvider;
use App\Integrations\Contracts\Providers\PaymentProvider;
use App\Integrations\Paystack\Entities\BankAccount;
use App\Integrations\Paystack\Entities\InitializePayment;
use App\Integrations\Paystack\Entities\PaymentResponse;
use App\Integrations\Paystack\Entities\RecipientResponse;
use Illuminate\Contracts\Events\Dispatcher;

class PaystackProvider implements AccountProvider, PaymentProvider
{
    public function __construct(
        protected array $config,
        protected Dispatcher $events,
        protected PaystackConnector $connector
    ) {}

    public function initializePayment(InitializePayment $entity): PaymentResponse
    {
        return $this->connector->transactions()->initialize($entity);
    }

    public function verifyPayment(string $reference): PaymentResponse
    {
        return $this->connector->transactions()->verify($reference);
    }

    public function createRecipient(BankAccount $entity): RecipientResponse
    {
        // Use recipients resource
        return $this->connector->recipients()->create($entity);
    }
}
