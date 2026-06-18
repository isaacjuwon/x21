<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseCable as PurchaseCableEntity;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

final class PurchaseCableAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(TopupTransaction $transaction): ServiceResponse
    {
        $entity = new PurchaseCableEntity(
            service: (string) $transaction->brand->api_code,
            smartcardNumber: (string) $transaction->recipient,
            variationCode: (string) $transaction->plan->api_code,
            amount: (int) $transaction->amount,
            reference: $transaction->reference,
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
                'status' => $response->isSuccessful() ? 'completed' : 'failed',
            ]);

            if ($response->isSuccessful()) {
                event(new ServicePurchased($transaction));
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Cable purchase failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ]);

            $transaction->update(['status' => 'failed']);

            throw $e;
        }
    }
}
