<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseData as PurchaseDataEntity;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\DataPlan;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

final class PurchaseDataAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(TopupTransaction $transaction): ServiceResponse
    {
        /** @var DataPlan $plan */
        $plan = $transaction->plan;
        $code = $plan->api_code;

        if ($code === null || $code === '') {
            throw new \RuntimeException(
                "DataPlan #{$plan->id} has no resolvable API code for failover chain "
                .json_encode(config('api.providers.failover.providers')).'. '
                .'Add provider API codes for this plan in admin.'
            );
        }

        $entity = new PurchaseDataEntity(
            network: (string) ($transaction->meta['network'] ?? $transaction->brand->api_code),
            mobileNumber: (string) $transaction->recipient,
            apiCode: (string) $code,
            reference: $transaction->reference,
            planId: $plan->id,
            planType: $plan::class,
        );

        try {
            $response = $this->apiManager->vtuProvider()->purchaseData($entity);

            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/data/',
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
                event(new ServicePurchased($transaction));
            } else {
                $transaction->fail('Data purchase unsuccessful: '.($response->description['response_description'] ?? 'Provider declined'));
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Data purchase failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ]);

            $transaction->fail('Data purchase exception: '.$e->getMessage());

            throw $e;
        }
    }
}
