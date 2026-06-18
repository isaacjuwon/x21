<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseElectricity as PurchaseElectricityEntity;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

final class PurchaseElectricityAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(TopupTransaction $transaction): ServiceResponse
    {
        $entity = new PurchaseElectricityEntity(
            service: (string) $transaction->brand->api_code,
            meterNumber: (string) $transaction->recipient,
            meterType: (string) ($transaction->meta['meter_type'] ?? 'prepaid'),
            amount: (int) $transaction->amount,
            reference: $transaction->reference,
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
                'status' => $response->isSuccessful() ? 'completed' : 'failed',
                'api_reference' => $response->description['ref'] ?? null,
                'response_message' => $response->description['response_description'] ?? 'Transaction processed',
            ]);

            if ($response->isSuccessful()) {
                event(new ServicePurchased($transaction));
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Electricity payment failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ]);

            $transaction->update(['status' => 'failed']);

            throw $e;
        }
    }
}
