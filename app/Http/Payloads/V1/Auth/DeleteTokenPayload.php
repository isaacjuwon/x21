<?php

declare(strict_types=1);

namespace App\Http\Payloads\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

final readonly class DeleteTokenPayload
{
    public function __construct(
        public int $tokenId,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request instanceof FormRequest ? $request->validated() : $request->all();

        return new self(tokenId: (int) $data['token_id']);
    }
}
