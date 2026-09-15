<?php

declare(strict_types=1);

namespace App\Actions\Vtu;

use App\Http\Payloads\V1\Services\ValidateMeterPayload;
use App\Integrations\Epins\Entities\ValidateMeter as ValidateMeterEntity;
use App\Integrations\Epins\Entities\ValidationResponse;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\Brand;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ValidateMeterAction
{
    public function __construct(
        private readonly ApiManager $apiManager,
    ) {}

    public function handle(ValidateMeterPayload $payload, ?int $userId = null): ValidationResponse
    {
        $brand = Brand::findOrFail($payload->brandId);

        $entity = new ValidateMeterEntity(
            service: (string) $brand->api_code,
            meterNumber: $payload->meterNumber,
            meterType: strtolower($payload->meterType),
        );

        $reference = 'VAL-MTR-'.strtoupper(Str::random(10));

        try {
            $response = $this->apiManager->vtuProvider()->validateMeter($entity);

            RecordApiRequestJob::dispatch(
                type: 'vtu',
                method: 'POST',
                url: '/electricity/validate-meter',
                payload: $entity->toRequestBody(),
                response: (array) $response,
                userId: $userId,
                reference: $reference,
            );

            return $response;

        } catch (\Exception $e) {
            Log::error('Meter validation failed: '.$e->getMessage(), [
                'brand_id' => $payload->brandId,
                'meter_number' => $payload->meterNumber,
                'meter_type' => $payload->meterType,
                'reference' => $reference,
            ]);

            throw $e;
        }
    }
}
