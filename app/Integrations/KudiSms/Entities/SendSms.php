<?php

declare(strict_types=1);

namespace App\Integrations\KudiSms\Entities;

final readonly class SendSms
{
    public function __construct(
        public string $senderId,
        public array $recipients,
        public string $message,
    ) {}

    /**
     * @return array{sender_id: string, recipients: array, message: string}
     */
    public function toRequestBody(): array
    {
        return [
            'sender_id' => $this->senderId,
            'recipients' => $this->recipients,
            'message' => $this->message,
        ];
    }
}
