<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseExam as PurchaseExamEntity;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

final class PurchaseEducationAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(TopupTransaction $transaction): ServiceResponse
    {
        $quantity = (int) ($transaction->meta['quantity'] ?? 1);

        $entity = new PurchaseExamEntity(
            service: (string) $transaction->brand->api_code,
            variationCode: (string) $transaction->plan->api_code,
            amount: (int) $transaction->amount,
            numberOfPins: $quantity,
            reference: $transaction->reference,
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
                'status' => $response->isSuccessful() ? 'completed' : 'failed',
                'response_message' => $response->isSuccessful() ? ($response->description['Content'] ?? 'Success') : 'Failed',
            ]);

            if ($response->isSuccessful()) {
                event(new ServicePurchased($transaction));
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Education pin purchase failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ]);

            $transaction->update(['status' => 'failed']);

            throw $e;
        }
    }
}
