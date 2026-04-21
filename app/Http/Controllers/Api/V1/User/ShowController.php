<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Resources\Api\V1\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('User', 'Authenticated user profile')]
#[Authenticated]
final class ShowController
{
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function __invoke(Request $request): UserResource
    {
        return new UserResource($request->user()->load('loanLevel', 'shareHolding', 'roles'));
    }
}
