<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\UpdateSharePriceAction;
use App\Http\Requests\Api\V1\Shares\UpdateSharePriceRequest;
use App\Http\Resources\Api\V1\Shares\ShareListingResource;
use App\Models\ShareListing;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
final class UpdateListingController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly UpdateSharePriceAction $action,
    ) {}

    #[BodyParam('price', 'number', description: 'New share price (min: 0.01)', required: true, example: 150.00)]
    #[ResponseFromApiResource(ShareListingResource::class, ShareListing::class)]
    #[Response(['message' => 'This action is unauthorized.'], status: 403)]
    public function __invoke(UpdateSharePriceRequest $request): ShareListingResource
    {
        $this->authorize('updatePrice', ShareListing::class);

        return new ShareListingResource($this->action->handle((float) $request->price));
    }
}
