<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Kyc;

use App\Http\Resources\Api\V1\Kyc\KycResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;

#[Group('KYC', 'Identity verification')]
#[Authenticated]
final class IndexController
{
    /**
     * Get the authenticated user's KYC status for all types.
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $kycs = $request->user()->kycs()->get();

        return KycResource::collection($kycs);
    }
}
