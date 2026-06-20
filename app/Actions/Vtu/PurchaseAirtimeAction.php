<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseAirtime as PurchaseAirtimeEntity;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

final class PurchaseAirtimeAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(TopupTransaction $transaction): ServiceResponse
    {
        $entity = new PurchaseAirtimeEntity(
            network: (string) ($transaction->meta['network'] ?? $transaction->brand->slug),
            amount: (int) $transaction->amount,
            mobileNumber: (string) $transaction->recipient,
            reference: $transaction->reference,
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
                'status' => $response->isSuccessful() ? 'completed' : 'failed',
                'api_reference' => $response->description['ref'] ?? null,
                'response_message' => $response->description['response_description'] ?? 'Transaction processed',
            ]);

            if ($response->isSuccessful()) {
                event(new ServicePurchased($transaction));
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Airtime purchase failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ]);

            $transaction->update(['status' => 'failed']);

            throw $e;
        }
    }
}
