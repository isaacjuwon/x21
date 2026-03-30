<?php

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseAirtime as PurchaseAirtimeEntity;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

class PurchaseAirtimeAction
{
    public function __construct(
        protected ApiManager $apiManager,
    ) {}

    /**
     * Purchase airtime using the configured VTU provider.
     *
     * @param TopupTransaction $transaction
     * @return \App\Integrations\Epins\Entities\ServiceResponse
     */
    public function handle(TopupTransaction $transaction)
    {
        $purchaseData = new PurchaseAirtimeEntity(
            network: $transaction->brand->network_code,
            amount: (int) $transaction->amount,
            mobileNumber: $transaction->phone_number,
            reference: $transaction->reference
        );

        try {
            $vtuProvider = $this->apiManager->vtuProvider();
            $response = $vtuProvider->purchaseAirtime($purchaseData);

            // Record the request using Job
            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/airtime/',
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
            Log::error("Airtime purchase failed: " . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference
            ]);

            $transaction->update(['status' => 'failed']);
            throw $e;
        }
    }
}
