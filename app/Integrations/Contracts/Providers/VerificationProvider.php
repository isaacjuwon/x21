<?php

namespace App\Integrations\Contracts\Providers;

use App\Integrations\Dojah\Entities\BvnMatchRequest;
use App\Integrations\Dojah\Entities\NinLookupRequest;
use App\Integrations\Dojah\Entities\VerificationResponse;

interface VerificationProvider
{
    /**
     * Verify NIN using lookup.
     */
    public function verifyNin(NinLookupRequest $request): VerificationResponse;

    /**
     * Verify BVN using match.
     */
    public function verifyBvn(BvnMatchRequest $request): VerificationResponse;
}
