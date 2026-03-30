<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Shares\DividendPayoutResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DividendPayoutController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return DividendPayoutResource::collection(
            auth()->user()->dividendPayouts()->latest()->paginate(15)
        );
    }
}
