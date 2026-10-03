<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseExam as PurchaseExamEntity;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\EducationPlan;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

final class PurchaseEducationAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(TopupTransaction $transaction): ServiceResponse
    {
        /** @var EducationPlan $plan */
        $plan = $transaction->plan;
        $quantity = (int) ($transaction->meta['quantity'] ?? 1);

        $entity = new PurchaseExamEntity(
            service: (string) $transaction->brand->api_code,
            apiCode: (string) $plan->api_code,
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
                event(new ServicePurchased($transaction));
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
