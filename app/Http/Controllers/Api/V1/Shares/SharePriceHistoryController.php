<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Shares\SharePriceHistoryResource;
use App\Models\SharePriceHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SharePriceHistoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return SharePriceHistoryResource::collection(
            SharePriceHistory::latest('created_at')->paginate(15)
        );
    }
}
