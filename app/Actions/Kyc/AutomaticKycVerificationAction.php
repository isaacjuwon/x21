<?php

namespace App\Actions\Kyc;

use App\Enums\Kyc\KycMethod;
use App\Enums\Kyc\KycStatus;
use App\Enums\Kyc\KycType;
use App\Integrations\Dojah\Entities\BvnMatchRequest;
use App\Integrations\Dojah\Entities\NinLookupRequest;
use App\Jobs\RecordApiRequestJob;
use App\Managers\ApiManager;
use App\Models\Kyc;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AutomaticKycVerificationAction
{
    public function __construct(
        protected ApiManager $apiManager,
    ) {}

    /**
     * Perform automatic KYC verification using Dojah.
     */
    public function handle(User $user, KycType $type, string $number): Kyc
    {
        $kyc = Kyc::updateOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            [
                'number' => $number,
                'method' => KycMethod::Automatic,
                'status' => KycStatus::Pending,
                'verified_at' => null,
            ]
        );

        $method = 'GET';
        $url = $type === KycType::Nin ? '/api/v1/kyc/nin' : '/api/v1/kyc/bvn';
        $payload = $type === KycType::Nin
            ? (new NinLookupRequest($number))->toQuery()
            : (new BvnMatchRequest($number, $user->first_name, $user->last_name))->toQuery();

        try {
            $verificationProvider = $this->apiManager->verificationProvider('dojah');

            $response = null;
            if ($type === KycType::Nin) {
                $response = $verificationProvider->verifyNin(new NinLookupRequest($number));
            } elseif ($type === KycType::Bvn) {
                $response = $verificationProvider->verifyBvn(new BvnMatchRequest($number, $user->first_name, $user->last_name));
            }

            $success = false;
            $rejectionReason = 'Verification failed';

            if ($response && $response->success) {
                if ($type === KycType::Nin) {
                    $success = ! empty($response->data['first_name']);
                    if (! $success) {
                        $rejectionReason = 'NIN Lookup failed';
                    }
                } elseif ($type === KycType::Bvn) {
                    $entity = $response->data['entity'] ?? null;
                    $success = $entity &&
                               ($entity['bvn']['status'] ?? false) === true &&
                               ($entity['first_name']['status'] ?? false) === true &&
                               ($entity['last_name']['status'] ?? false) === true;
                    if (! $success) {
                        $rejectionReason = 'BVN Match failed or details mismatch';
                    }
                }
            } else {
                $rejectionReason = $response?->message ?? 'Verification failed';
            }

            if ($success) {
                $kyc->update([
                    'status' => KycStatus::Verified,
                    'data' => $response->data,
                    'verified_at' => now(),
                ]);
            } else {
                $kyc->update([
                    'status' => KycStatus::Rejected,
                    'rejection_reason' => $rejectionReason,
                ]);
            }

            RecordApiRequestJob::dispatch(
                type: 'dojah',
                method: $method,
                url: $url,
                payload: $payload,
                response: $response ? [
                    'success' => $success,
                    'data' => $response->data,
                    'message' => $success ? 'Verification successful' : $rejectionReason,
                ] : ['error' => 'No response returned from provider'],
                userId: $user->id,
                reference: (string) $kyc->id,
            );

        } catch (\Exception $e) {
            Log::error("Automatic KYC failed for User {$user->id}: ".$e->getMessage());
            $kyc->update([
                'status' => KycStatus::Pending, // Keep as pending if API error? Or fail?
                'rejection_reason' => 'API connection error. Please try again later.',
            ]);

            RecordApiRequestJob::dispatch(
                type: 'dojah',
                method: $method,
                url: $url,
                payload: $payload,
                response: [
                    'success' => false,
                    'error' => $e->getMessage(),
                ],
                userId: $user->id,
                reference: (string) $kyc->id,
            );
        }

        return $kyc;
    }
}
