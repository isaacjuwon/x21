<?php

declare(strict_types=1);

namespace App\Http\Payloads\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

final readonly class ResetPasswordPayload
{
    public function __construct(
        public string $token,
        public string $email,
        public string $password,
        public string $passwordConfirmation,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request instanceof FormRequest ? $request->validated() : $request->all();

        return new self(
            token: (string) $data['token'],
            email: (string) $data['email'],
            password: (string) $data['password'],
            passwordConfirmation: (string) $data['password_confirmation'],
        );
    }
}
