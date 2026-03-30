<?php

namespace App\Actions\Vtu;

use App\Events\Services\ServicePurchased;
use App\Integrations\Epins\Entities\PurchaseExam as PurchaseExamEntity;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\TopupTransaction;
use Illuminate\Support\Facades\Log;

class PurchaseEducationAction
{
    public function __construct(
        protected ApiManager $apiManager,
    ) {}

    /**
     * Purchase education pins using the configured VTU provider.
     *
     * @param TopupTransaction $transaction
     * @return \App\Integrations\Epins\Entities\ServiceResponse
     */
    public function handle(TopupTransaction $transaction)
    {
        $purchaseData = new PurchaseExamEntity(
            service: $transaction->brand->slug, // e.g., waec, neco
            numberOfPins: (int) ($transaction->quantity ?? 1),
            reference: $transaction->reference
        );

        try {
            $vtuProvider = $this->apiManager->vtuProvider();
            $response = $vtuProvider->purchaseExam($purchaseData);

            // Record the request using Job
            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/education/',
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
            Log::error("Education pin purchase failed: " . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference
            ]);

            $transaction->update(['status' => 'failed']);
            throw $e;
        }
    }
}
