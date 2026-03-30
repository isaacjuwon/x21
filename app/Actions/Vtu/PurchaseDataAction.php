<?php

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseData as PurchaseDataEntity;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

class PurchaseDataAction
{
    public function __construct(
        protected ApiManager $apiManager,
    ) {}

    /**
     * Purchase data bundle using the configured VTU provider.
     *
     * @param TopupTransaction $transaction
     * @return \App\Integrations\Epins\Entities\ServiceResponse
     */
    public function handle(TopupTransaction $transaction)
    {
        $purchaseData = new PurchaseDataEntity(
            network: $transaction->brand->network_code, // Assuming mapping exists
            mobileNumber: $transaction->phone_number,
            dataCode: $transaction->plan->variation_code,
            reference: $transaction->reference
        );

        try {
            $vtuProvider = $this->apiManager->vtuProvider();
            $response = $vtuProvider->purchaseData($purchaseData);

            // Record the request using Job
            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/data/',
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
            Log::error("Data purchase failed: " . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference
            ]);

            $transaction->update(['status' => 'failed']);
            throw $e;
        }
    }
}
