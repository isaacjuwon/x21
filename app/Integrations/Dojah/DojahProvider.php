<?php

namespace App\Integrations\Dojah;

use App\Integrations\Contracts\Providers\VerificationProvider;
use App\Integrations\Dojah\Entities\BvnMatchRequest;
use App\Integrations\Dojah\Entities\NinLookupRequest;
use App\Integrations\Dojah\Entities\VerificationResponse;
use Illuminate\Contracts\Events\Dispatcher;

class DojahProvider implements VerificationProvider
{
    public function __construct(
        protected array $config,
        protected Dispatcher $events,
        protected DojahConnector $connector
    ) {}

    /**
     * Verify NIN using lookup.
     */
    public function verifyNin(NinLookupRequest $request): VerificationResponse
    {
        return $this->connector->verification()->ninLookup($request);
    }

    /**
     * Verify BVN using match.
     */
    public function verifyBvn(BvnMatchRequest $request): VerificationResponse
    {
        return $this->connector->verification()->bvnMatch($request);
    }
}
