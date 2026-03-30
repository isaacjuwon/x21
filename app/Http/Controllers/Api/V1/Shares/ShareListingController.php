<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\UpdateSharePriceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shares\UpdateSharePriceRequest;
use App\Http\Resources\Api\V1\Shares\ShareListingResource;
use App\Models\ShareListing;
use Illuminate\Http\Request;

class ShareListingController extends Controller
{
    public function show(Request $request): ShareListingResource
    {
        return new ShareListingResource(ShareListing::firstOrFail());
    }

    public function update(UpdateSharePriceRequest $request, UpdateSharePriceAction $action): ShareListingResource
    {
        $this->authorize('updatePrice', ShareListing::class);

        $listing = $action->handle($request->price);

        return new ShareListingResource($listing);
    }
}
