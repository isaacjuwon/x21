<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateUserRequest $request, UpdateUserAction $action): UserResource
    {
        $user = $action->handle($request->user(), $request->payload());

        return new UserResource($user);
    }
}
