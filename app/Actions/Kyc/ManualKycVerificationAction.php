<?php

namespace App\Actions\Kyc;

use App\Enums\Kyc\KycMethod;
use App\Enums\Kyc\KycStatus;
use App\Enums\Kyc\KycType;
use App\Models\Kyc;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ManualKycVerificationAction
{
    /**
     * Submit a manual KYC verification request.
     */
    public function handle(User $user, KycType $type, string $number, $file): Kyc
    {
        $path = $file->store("kyc/{$user->id}", 'public');

        return Kyc::updateOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            [
                'number' => $number,
                'method' => KycMethod::Manual,
                'status' => KycStatus::Pending,
                'file_path' => $path,
                'rejection_reason' => null,
                'verified_at' => null,
            ]
        );
    }
}
