<?php

namespace App\Actions\Kyc;

use App\Enums\Kyc\Status as KycStatusEnum;
use App\Events\Kyc\KycVerificationCompleted;
use App\Events\Kyc\KycVerificationFailed;
use App\Events\Kyc\KycVerificationVerified;
use App\Enums\Connectors\KycConnector;
use App\Integrations\Dojah\Entities\BvnMatchRequest;
use App\Integrations\Dojah\Entities\NinLookupRequest;
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
            $verificationResource = $connector->verification();

            if ($kyc->type === 'bvn') {
                $request = new BvnMatchRequest(
                    bvn: $kyc->id_number,
                    firstName: $user->first_name,
                    lastName: $user->last_name,
                    dob: $kyc->meta['dob'] ?? null,
                );
                $response = $verificationResource->bvnMatch($request);
            } elseif ($kyc->type === 'nin') {
                $request = new NinLookupRequest(
                    nin: $kyc->id_number,
                );
                $response = $verificationResource->ninLookup($request);
            } else {
                // Fallback to generic verify if type is unexpected
                $request = new VerificationRequest(
                    firstName: $user->first_name,
                    lastName: $user->last_name,
                    idType: $kyc->type,
                    idNumber: $kyc->id_number,
                    dob: $kyc->meta['dob'] ?? null,
                    phone: $kyc->meta['phone'] ?? null,
                    email: $kyc->meta['email'] ?? null,
                );
                $response = $verificationResource->verify($request);
            }

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
