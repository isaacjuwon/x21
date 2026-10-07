<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Http\Entities\PurchaseElectricity as PurchaseElectricityEntity;
use App\Http\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\Plan;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

final class PurchaseElectricityAction
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

        $entity = new PurchaseElectricityEntity(
            service: (string) $transaction->brand->api_code,
            meterNumber: (string) $transaction->recipient,
            meterType: (string) ($transaction->meta['meter_type'] ?? 'prepaid'),
            apiCode: $code,
            amount: (int) $transaction->amount,
            reference: $transaction->reference,
            planId: $plan->id,
            planType: $plan::class,
        );

        try {
            $response = $this->apiManager->vtuProvider()->purchaseElectricity($entity);

            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/electricity/',
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
                $transaction->fail('Electricity payment unsuccessful: '.($response->description['response_description'] ?? 'Provider declined'));
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Electricity payment failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ]);

            $transaction->fail('Electricity payment exception: '.$e->getMessage());

            throw $e;
        }
    }
}
