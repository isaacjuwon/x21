<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Kyc\CreateKycVerificationAction;
use App\Actions\Kyc\VerificationAction;
use App\Enums\Kyc\VerificationMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Kyc\KycVerificationRequest;
use App\Http\Resources\KycResource;
use App\Models\KycVerification;
use App\Settings\VerificationSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class KycController extends Controller
{
    /**
     * List user's KYC verification history.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $kyc = KycVerification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return KycResource::collection($kyc);
    }

    /**
     * Submit a KYC verification request.
     */
    public function verify(
        KycVerificationRequest $request,
        CreateKycVerificationAction $createAction,
        VerificationAction $verifyAction
    ): \Illuminate\Http\JsonResponse {
        $payload = $request->payload();

        // Create KYC record
        $kyc = DB::transaction(function () use ($createAction, $request, $payload) {
            return $createAction->handle($request->user(), $payload->toArray());
        });

        // Check verification mode
        $settings = app(VerificationSettings::class);
        $mode = VerificationMode::tryFrom($settings->kyc_verification_mode) ?? VerificationMode::Automatic;

        if ($mode === VerificationMode::Automatic) {
            // Process immediately if automatic
            // Assuming VerificationAction::handle also wraps in transaction if complex, or we rely on it.
            // Since kyc record is already created, we can process it.
            // Note: If configured to run via Event Listener, this might be redundant.
            // However, explicit call ensures API response can reflect immediate status if synchronous.
            // VerificationAction returns Result.

            $result = $verifyAction->handle($kyc);

            if (! $result->success) {
                // If it fails, we still return the created record but status is failed.
                // Or we can let the flow continue.
                // The Action updates the status.
            }

            $kyc->refresh();
        }

        return response()->json([
            'message' => 'Verification request submitted.',
            'data' => new KycResource($kyc),
        ]);
    }
}
