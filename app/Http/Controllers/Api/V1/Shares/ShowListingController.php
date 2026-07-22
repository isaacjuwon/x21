<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shares;

use App\Settings\ShareSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
final class ShowListingController
{
    #[Response(['data' => ['price' => 150.00]])]
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'price' => app(ShareSettings::class)->price_per_share,
            ],
        ]);
    }
}
