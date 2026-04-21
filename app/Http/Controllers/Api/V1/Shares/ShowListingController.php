<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shares;

use App\Http\Resources\Api\V1\Shares\ShareListingResource;
use App\Models\ShareListing;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
final class ShowListingController
{
    #[ResponseFromApiResource(ShareListingResource::class, ShareListing::class)]
    public function __invoke(Request $request): ShareListingResource
    {
        return new ShareListingResource(ShareListing::firstOrFail());
    }
}
