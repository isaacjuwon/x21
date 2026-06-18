<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseData as PurchaseDataEntity;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

final class PurchaseDataAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(TopupTransaction $transaction): ServiceResponse
    {
        $entity = new PurchaseDataEntity(
            network: (string) ($transaction->meta['network'] ?? $transaction->brand->api_code),
            mobileNumber: (string) $transaction->recipient,
            dataCode: (string) $transaction->plan->api_code,
            reference: $transaction->reference,
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
                'status' => $response->isSuccessful() ? 'completed' : 'failed',
            ]);

            if ($response->isSuccessful()) {
                event(new ServicePurchased($transaction));
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Data purchase failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ]);

            $transaction->update(['status' => 'failed']);

            throw $e;
        }
    }
}
