<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Resources\Api\V1\User\UserResource;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Authentication', 'Obtain and revoke Sanctum tokens')]
#[Authenticated]
class MeController
{
    #[ResponseFromApiResource(UserResource::class, \App\Models\User::class)]
    public function __invoke(Request $request): UserResource
    {
        return new UserResource(
            $request->user()->load('loanLevel', 'shareHolding')
        );
    }
}
