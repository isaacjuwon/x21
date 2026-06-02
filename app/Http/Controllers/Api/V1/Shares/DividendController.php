<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\DeclareDividendAction;
use App\Http\Requests\Api\V1\Shares\StoreDividendRequest;
use App\Models\ShareOrder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
class DividendController
{
    use AuthorizesRequests;

    #[BodyParam('percentage', 'number', description: 'Dividend percentage of the share price (min: 0.01)', required: true, example: 2.5)]

    #[Response(['message' => 'Dividend declared successfully.'], status: 201)]
    #[Response(['message' => 'This action is unauthorized.'], status: 403)]
    public function __invoke(StoreDividendRequest $request, DeclareDividendAction $action): JsonResponse
    {
        $this->authorize('declareDividend', ShareOrder::class);

        $action->handle((float) $request->percentage);

        return response()->json(['message' => 'Dividend declared successfully.'], 201);
    }
}
