<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\UpdateSharePriceAction;
use App\Http\Requests\Api\V1\Shares\UpdateSharePriceRequest;
use App\Http\Resources\Api\V1\Shares\ShareListingResource;
use App\Models\ShareListing;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
class ShareListingController
{
    use AuthorizesRequests;

    #[ResponseFromApiResource(ShareListingResource::class, ShareListing::class)]
    public function show(Request $request): ShareListingResource
    {
        return new ShareListingResource(ShareListing::firstOrFail());
    }

    #[BodyParam('price', 'number', description: 'New share price (min: 0.01)', required: true, example: 150.00)]
    #[ResponseFromApiResource(ShareListingResource::class, ShareListing::class)]
    #[Response(['message' => 'This action is unauthorized.'], status: 403)]
    public function update(UpdateSharePriceRequest $request, UpdateSharePriceAction $action): ShareListingResource
    {
        $this->authorize('updatePrice', ShareListing::class);

        return new ShareListingResource($action->handle((float) $request->price));
    }
}
