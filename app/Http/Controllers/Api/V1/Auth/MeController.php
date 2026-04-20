<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Resources\Api\V1\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Authentication', 'Register, login, and manage tokens')]
#[Authenticated]
final class MeController
{
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource(
                $request->user()->load('loanLevel', 'shareHolding')
            ),
        ]);
    }
}
