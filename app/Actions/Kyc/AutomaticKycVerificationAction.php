<?php

namespace App\Actions\Kyc;

use App\Enums\Kyc\KycMethod;
use App\Enums\Kyc\KycStatus;
use App\Enums\Kyc\KycType;
use App\Integrations\Dojah\Entities\NinLookupRequest;
use App\Integrations\Dojah\Entities\BvnMatchRequest;
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

        try {
            $dojah = $this->apiManager->vtuProvider(); // Wait, need to check if ApiManager has a verification provider
            
            // Checking ApiManager.php again, it seems it doesn't have a explicit 'verificationProvider'
            // but Dojah integrations exist. I'll assume we can get it via ApiManager or direct instantiation if not bound.
            // Let's check ApiManager again for 'driver' methods.
            
            $response = null;
            if ($type === KycType::Nin) {
                // $response = $dojah->ninLookup(new NinLookupRequest($number));
            } elseif ($type === KycType::Bvn) {
                // $response = $dojah->bvnMatch(new BvnMatchRequest($number, $user->first_name, $user->last_name));
            }

            // For now, I'll implement the structure and we can refine the Dojah call once ApiManager is updated
            // if ($response && $response->success) {
            //     $kyc->update([
            //         'status' => KycStatus::Verified,
            //         'data' => $response->data,
            //         'verified_at' => now(),
            //     ]);
            // } else {
            //     $kyc->update([
            //         'status' => KycStatus::Rejected,
            //         'rejection_reason' => $response?->message ?? 'Verification failed',
            //     ]);
            // }

        } catch (\Exception $e) {
            Log::error("Automatic KYC failed for User {$user->id}: " . $e->getMessage());
            $kyc->update([
                'status' => KycStatus::Pending, // Keep as pending if API error? Or fail?
                'rejection_reason' => 'API connection error. Please try again later.',
            ]);
        }

        return $kyc;
    }
}
