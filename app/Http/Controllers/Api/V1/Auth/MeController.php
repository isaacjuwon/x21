<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Resources\Api\V1\User\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource(
                $request->user()->load('loanLevel', 'shareHolding')
            ),
        ]);
    }
}
