<?php

declare(strict_types=1);

namespace App\Http\Payloads\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

final readonly class RegisterPayload
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $deviceName,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request instanceof FormRequest ? $request->validated() : $request->all();

        return new self(
            name: (string) $data['name'],
            email: (string) $data['email'],
            password: (string) $data['password'],
            deviceName: isset($data['device_name']) && $data['device_name'] !== ''
                ? (string) $data['device_name']
                : 'api-client',
        );
    }
}
