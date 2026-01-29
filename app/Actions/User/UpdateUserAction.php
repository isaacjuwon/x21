<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Http\Payloads\User\UpdateUserPayload;
use App\Models\User;

class UpdateUserAction
{
    public function handle(User $user, UpdateUserPayload $payload): User
    {
        $user->forceFill([
            'name' => $payload->name,
            'email' => $payload->email,
        ]);

        if ($payload->phoneNumber !== null) {
            $user->phone_number = $payload->phoneNumber;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}
