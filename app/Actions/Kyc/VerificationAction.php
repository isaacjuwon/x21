<?php

namespace App\Actions\Kyc;

use App\Enums\Kyc\Status as KycStatusEnum;
use App\Events\Kyc\KycVerificationCompleted;
use App\Events\Kyc\KycVerificationFailed;
use App\Events\Kyc\KycVerificationVerified;
use App\Enums\Connectors\KycConnector;
use App\Integrations\Dojah\Entities\VerificationRequest;
use App\Models\KycVerification;
use App\Support\Result;
use Exception;
use Illuminate\Support\Facades\Auth;

class VerificationAction
{
    public function handle(KycVerification $kyc): Result
    {
        try {
            $user = Auth::user();

            // Resolve KYC connector (default: Dojah)
            $connector = KycConnector::default()->connector();
            $request = new VerificationRequest(
                firstName: $user->first_name,
                lastName: $user->last_name,
                idType: $kyc->type,
                idNumber: $kyc->id_number,
                dob: $kyc->meta['dob'] ?? null,
                phone: $kyc->meta['phone'] ?? null,
                email: $kyc->meta['email'] ?? null,
            );
            $response = $connector->verification()->verify($request);

            $kyc->status = $response->success ? KycStatusEnum::Verified : KycStatusEnum::Failed;
            $kyc->response = $response->data;
            $kyc->save();

            if ($response->success) {
                event(new KycVerificationVerified($kyc));
            } else {
                event(new KycVerificationFailed($kyc));
            }

            event(new KycVerificationCompleted($kyc));

            return Result::ok($kyc);
        } catch (Exception $e) {
            return Result::error($e);
        }
    }
}
