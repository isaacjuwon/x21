<?php

namespace App\Listeners\Kyc;

use App\Events\Kyc\KycVerificationCompleted;
use App\Notifications\Kyc\KycVerificationApproved;
use App\Enums\Kyc\Status as KycStatusEnum;

class SendKycVerificationNotification
{
    public function handle(KycVerificationCompleted $event): void
    {
        // Only send notification if verification was successful
        if ($event->kycVerification->status === KycStatusEnum::Verified) {
            $event->kycVerification->user->notify(
                new KycVerificationApproved($event->kycVerification)
            );
        }
    }
}
