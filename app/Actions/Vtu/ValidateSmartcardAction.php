<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Http\Payloads\V1\Services\ValidateSmartcardPayload;
use App\Integrations\Epins\Entities\ValidateSmartcard as ValidateSmartcardEntity;
use App\Integrations\Epins\Entities\ValidationResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\Brand;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ValidateSmartcardAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(ValidateSmartcardPayload $payload, ?int $userId = null): ValidationResponse
    {
        $brand = Brand::findOrFail($payload->brandId);

        $entity = new ValidateSmartcardEntity(
            service: (string) $brand->api_code,
            smartcardNumber: $payload->smartCardNumber,
        );

        $reference = 'VAL-SMC-'.strtoupper(Str::random(10));

        try {
            $response = $this->apiManager->vtuProvider()->validateSmartcard($entity);

            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/cable/validate-smartcard',
                payload: $entity->toRequestBody(),
                response: (array) $response,
                userId: $userId,
                reference: $reference,
            );

            return $response;

        } catch (\Exception $e) {
            Log::error('Smartcard validation failed: '.$e->getMessage(), [
                'brand_id' => $payload->brandId,
                'smartcard_number' => $payload->smartCardNumber,
                'reference' => $reference,
            ]);

            throw $e;
        }
    }
}
