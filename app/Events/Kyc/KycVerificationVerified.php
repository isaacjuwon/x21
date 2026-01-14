<?php

declare(strict_types=1);

namespace App\Events\Kyc;

use App\Models\KycVerification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KycVerificationVerified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public KycVerification $kycVerification
    ) {}
}
