<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Http\Entities\PurchaseAirtime as PurchaseAirtimeEntity;
use App\Http\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\Plan;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

final class PurchaseAirtimeAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(TopupTransaction $transaction): ServiceResponse
    {
        /** @var Plan $plan */
        $plan = $transaction->plan;

        $entity = new PurchaseAirtimeEntity(
            network: (string) ($transaction->meta['network'] ?? $transaction->brand->api_code),
            amount: (int) $transaction->amount,
            mobileNumber: (string) $transaction->recipient,
            reference: $transaction->reference,
            planId: $plan->id,
            planType: $plan::class,
        );

        try {
            $response = $this->apiManager->vtuProvider()->purchaseAirtime($entity);

            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/airtime/',
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
                $transaction->fail('Airtime purchase unsuccessful: '.($response->description['response_description'] ?? 'Provider declined'));
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Airtime purchase failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ]);

            $transaction->fail('Airtime purchase exception: '.$e->getMessage());

            throw $e;
        }
    }
}
