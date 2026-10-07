<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Http\Entities\PurchaseCable as PurchaseCableEntity;
use App\Http\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\Plan;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

final class PurchaseCableAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(TopupTransaction $transaction): ServiceResponse
    {
        /** @var Plan $plan */
        $plan = $transaction->plan;
        $code = $plan->api_code;

        if ($code === null || $code === '') {
            throw new \RuntimeException(
                "Plan #{$plan->id} has no resolvable API code for failover chain "
                .json_encode(config('api.providers.failover.providers')).'. '
                .'Add provider API codes for this plan in admin.'
            );
        }

        $entity = new PurchaseCableEntity(
            service: (string) $transaction->brand->api_code,
            smartcardNumber: (string) $transaction->recipient,
            apiCode: $code,
            amount: (int) $transaction->amount,
            reference: $transaction->reference,
            planId: $plan->id,
            planType: $plan::class,
        );

        try {
            $response = $this->apiManager->vtuProvider()->purchaseCable($entity);

            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/cable/',
                payload: $entity->toRequestBody(),
                response: (array) $response,
                userId: $transaction->user_id,
                reference: $transaction->reference,
            );

            $transaction->update([
                'api_reference' => $response->description['ref'] ?? null,
                'response_message' => $response->description['response_description'] ?? 'Transaction processed',
            ]);

            if ($response->isSuccessful()) {
                $transaction->update(['status' => 'completed']);
                event(new ServicePurchased($transaction, $plan));
            } else {
                $transaction->fail('Cable purchase unsuccessful: '.($response->description['response_description'] ?? 'Provider declined'));
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Cable purchase failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ]);

            $transaction->fail('Cable purchase exception: '.$e->getMessage());

            throw $e;
        }
    }
}
