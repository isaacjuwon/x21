<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Kyc;

use App\Actions\Kyc\ManualKycVerificationAction;
use App\Enums\Kyc\KycType;
use App\Http\Requests\Api\V1\Kyc\ManualKycRequest;
use App\Http\Resources\Api\V1\Kyc\KycResource;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;

#[Group('KYC', 'Identity verification')]
#[Authenticated]
final class ManualController
{
    public function __construct(
        private readonly ManualKycVerificationAction $action,
    ) {}

    /**
     * Submit manual KYC verification with document upload.
     * Stays pending until admin reviews the document.
     */
    public function __invoke(ManualKycRequest $request): JsonResponse
    {
        $kyc = $this->action->handle(
            user: $request->user(),
            type: KycType::from($request->validated('type')),
            number: $request->validated('number'),
            document: $request->file('document'),
        );

        return (new KycResource($kyc))
            ->additional(['message' => 'Document submitted successfully. Our team will review it shortly.'])
            ->response()
            ->setStatusCode(201);
    }
}
