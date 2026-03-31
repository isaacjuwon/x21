<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Requests\Api\V1\User\UpdateProfileRequest;
use App\Http\Resources\Api\V1\User\UserResource;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('User', 'Authenticated user profile')]
#[Authenticated]
class UserController
{
    #[ResponseFromApiResource(UserResource::class, \App\Models\User::class)]
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->load('loanLevel', 'shareHolding'));
    }

    #[ResponseFromApiResource(UserResource::class, \App\Models\User::class)]
    public function update(UpdateProfileRequest $request): UserResource
    {
        $request->user()->update($request->validated());

        return new UserResource($request->user()->fresh());
    }
}
