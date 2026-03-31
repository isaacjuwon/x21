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
        $user = $request->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $validated = $request->safe()->except('avatar');
        $user->fill($validated);
        $user->save();

        return new UserResource($user->fresh()->load('loanLevel', 'shareHolding'));
    }
}
