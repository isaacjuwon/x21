<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Requests\Api\V1\User\UpdateProfileRequest;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('User', 'Authenticated user profile')]
#[Authenticated]
final class UpdateController
{
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function __invoke(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $validated = $request->safe()->except('avatar');
        $user->fill($validated);
        $user->save();

        return new UserResource($user->fresh()->load('loanLevel', 'shareHolding', 'roles'));
    }
}
