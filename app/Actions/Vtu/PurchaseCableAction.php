<?php

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseCable as PurchaseCableEntity;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

class PurchaseCableAction
{
    public function __construct(
        protected ApiManager $apiManager,
    ) {}

    /**
     * Purchase cable subscription using the configured VTU provider.
     *
     * @return ServiceResponse
     */
    public function handle(TopupTransaction $transaction)
    {
        $purchaseData = new PurchaseCableEntity(
            service: $transaction->brand->slug, // e.g., dstv
            smartcardNumber: $transaction->smart_card_number,
            variationCode: $transaction->plan->api_code,
            reference: $transaction->reference,
            phone: $transaction->user->phone
        );

        try {
            $vtuProvider = $this->apiManager->vtuProvider();
            $response = $vtuProvider->purchaseCable($purchaseData);

            // Record the request using Job
            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/cable/',
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
            Log::error('Cable purchase failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ]);

            $transaction->update(['status' => 'failed']);
            throw $e;
        }
    }
}
