<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Kyc\CreateKycVerificationAction;
use App\Actions\Kyc\VerificationAction;
use App\Enums\Kyc\VerificationMode;
use App\Http\Requests\Api\Kyc\KycVerificationRequest;
use App\Http\Resources\KycResource;
use App\Models\KycVerification;
use App\Settings\VerificationSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KycController extends ApiController
{
    /**
     * List user's KYC verification history.
     */
    public function index(Request $request): JsonResponse
    {
        $kyc = KycVerification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);
        
        $kyc->setCollection($kyc->getCollection()->mapInto(KycResource::class));

        return $this->paginatedResponse($kyc, 'KYC history retrieved successfully');
    }

    /**
     * Submit a KYC verification request.
     */
    public function verify(
        KycVerificationRequest $request,
        CreateKycVerificationAction $createAction,
        VerificationAction $verifyAction
    ): JsonResponse {
        $payload = $request->payload();

        // Create KYC record
        $kyc = DB::transaction(function () use ($createAction, $request, $payload) {
            return $createAction->handle($request->user(), $payload->toArray());
        });

        // Check verification mode
        $settings = app(VerificationSettings::class);
        $mode = VerificationMode::tryFrom($settings->kyc_verification_mode) ?? VerificationMode::Automatic;

        if ($mode === VerificationMode::Automatic) {
            $result = $verifyAction->handle($kyc);
            
            // Re-fetch to get updated status
            $kyc->refresh();
        }

        return $this->createdResponse(new KycResource($kyc), 'Verification request submitted.');
    }
}
