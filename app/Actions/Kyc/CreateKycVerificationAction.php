<?php

namespace App\Actions\Kyc;

use App\Enums\Kyc\Status as KycStatusEnum;
use App\Enums\Kyc\VerificationMode;
use App\Events\Kyc\KycVerificationRequested;
use App\Models\KycVerification;
use App\Models\User;
use App\Settings\VerificationSettings;

class CreateKycVerificationAction
{
    public function handle(User $user, array $data): KycVerification
    {
        // Get verification mode from settings
        $verificationSettings = app(VerificationSettings::class);
        $verificationMode = VerificationMode::tryFrom($verificationSettings->kyc_verification_mode) ?? VerificationMode::Automatic;

        // Create KYC record
        $kyc = KycVerification::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'id_number' => $data['id_number'],
            'dob' => $data['dob'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'status' => KycStatusEnum::Pending,
            'verification_mode' => $verificationMode,
            'document_path' => $data['document_path'] ?? null,
        ]);

        event(new KycVerificationRequested($kyc));

        return $kyc;
    }
}
