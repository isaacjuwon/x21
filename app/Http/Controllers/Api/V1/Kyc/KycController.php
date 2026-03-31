<?php

namespace App\Http\Controllers\Api\V1\Kyc;

use App\Actions\Kyc\AutomaticKycVerificationAction;
use App\Actions\Kyc\ManualKycVerificationAction;
use App\Enums\Kyc\KycStatus;
use App\Enums\Kyc\KycType;
use App\Http\Requests\Api\V1\Kyc\AutomaticKycRequest;
use App\Http\Requests\Api\V1\Kyc\ManualKycRequest;
use App\Http\Resources\Api\V1\Kyc\KycResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;

#[Group('KYC', 'Identity verification')]
#[Authenticated]
class KycController
{
    /**
     * Get the authenticated user's KYC status for all types.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $kycs = $request->user()->kycs()->get();

        return KycResource::collection($kycs);
    }

    /**
     * Submit automatic KYC verification via Dojah.
     * Returns immediately with the verification result.
     */
    public function automatic(AutomaticKycRequest $request, AutomaticKycVerificationAction $action): JsonResponse
    {
        $kyc = $action->handle(
            $request->user(),
            KycType::from($request->validated('type')),
            $request->validated('number'),
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

    /**
     * Submit manual KYC verification with document upload.
     * Stays pending until admin reviews the document.
     */
    public function manual(ManualKycRequest $request, ManualKycVerificationAction $action): JsonResponse
    {
        $kyc = $action->handle(
            $request->user(),
            KycType::from($request->validated('type')),
            $request->validated('number'),
            $request->file('document'),
        );

        return (new KycResource($kyc))
            ->additional(['message' => 'Document submitted successfully. Our team will review it shortly.'])
            ->response()
            ->setStatusCode(201);
    }
}
