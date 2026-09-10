<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shares;

use App\Http\Requests\Api\V1\Shares\UpdateSharePriceRequest;
use App\Models\ShareOrder;
use App\Models\SharePriceHistory;
use App\Settings\ShareSettings;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
final class UpdateListingController
{
    use AuthorizesRequests;

    #[BodyParam('price', 'number', description: 'New share price (min: 0.01)', required: true, example: 150.00)]
    #[Response(['data' => ['price' => 150.00]])]
    #[Response(['message' => 'This action is unauthorized.'], status: 403)]
    public function __invoke(UpdateSharePriceRequest $request, ShareSettings $settings): JsonResponse
    {
        $this->authorize('updatePrice', ShareOrder::class);

        $oldPrice = $settings->price_per_share;
        $newPrice = (float) $request->price;

        SharePriceHistory::create([
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'created_at' => now(),
        ]);

        $settings->price_per_share = $newPrice;
        $settings->save();

        return response()->json([
            'data' => [
                'price' => $settings->price_per_share,
            ],
        ]);
    }
}
