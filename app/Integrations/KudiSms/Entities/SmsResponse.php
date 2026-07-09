<?php

declare(strict_types=1);

namespace App\Integrations\KudiSms\Entities;

final readonly class SmsResponse
{
    public function __construct(
        public string $code,
        public string $message,
        public ?string $messageId = null,
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            code: (string) ($data['code'] ?? $data['status'] ?? ''),
            message: (string) ($data['message'] ?? $data['description'] ?? ''),
            messageId: isset($data['message_id']) ? (string) $data['message_id'] : null,
        );
    }

    public function isSuccessful(): bool
    {
        return $this->code === '000';
    }
}
