<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Http\Entities\PurchaseExam as PurchaseExamEntity;
use App\Http\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\Plan;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

final class PurchaseEducationAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(TopupTransaction $transaction): ServiceResponse
    {
        /** @var Plan $plan */
        $plan = $transaction->plan;
        $quantity = (int) ($transaction->meta['quantity'] ?? 1);
        $code = $plan->api_code;

        if ($code === null || $code === '') {
            throw new \RuntimeException(
                "Plan #{$plan->id} has no resolvable API code for failover chain "
                .json_encode(config('api.providers.failover.providers')).'. '
                .'Add provider API codes for this plan in admin.'
            );
        }

        $entity = new PurchaseExamEntity(
            service: (string) $transaction->brand->api_code,
            apiCode: $code,
            amount: (int) $transaction->amount,
            numberOfPins: $quantity,
            reference: $transaction->reference,
            planId: $plan->id,
            planType: $plan::class,
        );

        try {
            $response = $this->apiManager->vtuProvider()->purchaseExam($entity);

            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/education/',
                payload: $entity->toRequestBody(),
                response: (array) $response,
                userId: $transaction->user_id,
                reference: $transaction->reference,
            );

            $transaction->update([
                'response_message' => $response->isSuccessful() ? ($response->description['Content'] ?? 'Success') : 'Failed',
            ]);

            if ($response->isSuccessful()) {
                $transaction->update(['status' => 'completed']);
                event(new ServicePurchased($transaction, $plan));
            } else {
                $transaction->fail('Education pin purchase unsuccessful: '.($response->description['response_description'] ?? 'Provider declined'));
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Education pin purchase failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ]);

            $transaction->fail('Education pin purchase exception: '.$e->getMessage());

            throw $e;
        }
    }
}
