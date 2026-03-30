<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\DeclareDividendAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shares\StoreDividendRequest;
use App\Models\ShareOrder;
use Illuminate\Http\JsonResponse;

class DividendController extends Controller
{
    public function store(StoreDividendRequest $request, DeclareDividendAction $action): JsonResponse
    {
        $this->authorize('declareDividend', ShareOrder::class);

        $action->handle($request->total_amount);

        return response()->json(['message' => 'Dividend declared successfully.'], 201);
    }
}
