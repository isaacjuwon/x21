<?php

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseElectricity as PurchaseElectricityEntity;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

class PurchaseElectricityAction
{
    public function __construct(
        protected ApiManager $apiManager,
    ) {}

    /**
     * Purchase electricity using the configured VTU provider.
     *
     * @param TopupTransaction $transaction
     * @return \App\Integrations\Epins\Entities\ServiceResponse
     */
    public function handle(TopupTransaction $transaction)
    {
        $purchaseData = new PurchaseElectricityEntity(
            service: $transaction->brand->slug, // e.g., ikeja-electric
            meterNumber: $transaction->meter_number,
            amount: (int) $transaction->amount,
            reference: $transaction->reference,
            phone: $transaction->user->phone // Assuming user has phone
        );

        try {
            $vtuProvider = $this->apiManager->vtuProvider();
            $response = $vtuProvider->purchaseElectricity($purchaseData);

            // Record the request using Job
            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/electricity/',
                payload: $purchaseData->toRequestBody(),
                response: (array) $response,
                userId: $transaction->user_id,
                reference: $transaction->reference
            );

            if ($response->isSuccessful()) {
                $transaction->update(['status' => 'completed']);
                event(new ServicePurchased($transaction));
            } else {
                $transaction->update(['status' => 'failed']);
            }

            return $response;

        } catch (\Exception $e) {
            Log::error("Electricity payment failed: " . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference
            ]);

            $transaction->update(['status' => 'failed']);
            throw $e;
        }
    }
}
