<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\UpdateUserAction;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse(new UserResource($request->user()), 'User profile retrieved successfully');
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateUserRequest $request, UpdateUserAction $action): JsonResponse
    {
        $user = $action->handle($request->user(), $request->payload());

        return $this->successResponse(new UserResource($user), 'User profile updated successfully');
    }
}
