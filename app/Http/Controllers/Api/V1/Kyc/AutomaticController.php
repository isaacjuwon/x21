<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Kyc;

use App\Actions\Kyc\AutomaticKycVerificationAction;
use App\Enums\Kyc\KycStatus;
use App\Enums\Kyc\KycType;
use App\Http\Requests\Api\V1\Kyc\AutomaticKycRequest;
use App\Http\Resources\Api\V1\Kyc\KycResource;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;

#[Group('KYC', 'Identity verification')]
#[Authenticated]
final class AutomaticController
{
    public function __construct(
        private readonly AutomaticKycVerificationAction $action,
    ) {}

    /**
     * Submit automatic KYC verification via Dojah.
     * Returns immediately with the verification result.
     */
    public function __invoke(AutomaticKycRequest $request): JsonResponse
    {
        $kyc = $this->action->handle(
            user: $request->user(),
            type: KycType::from($request->validated('type')),
            number: $request->validated('number'),
        );

        $message = match ($kyc->status) {
            KycStatus::Verified => 'Verification successful.',
            KycStatus::Rejected => 'Verification failed. Please try manual upload.',
            default => 'Verification pending. Please try manual upload if this persists.',
        };

        return (new KycResource($kyc))
            ->additional(['message' => $message])
            ->response()
            ->setStatusCode($kyc->status === KycStatus::Verified ? 200 : 422);
    }
}
