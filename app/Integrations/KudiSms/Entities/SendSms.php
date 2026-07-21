<?php

declare(strict_types=1);

namespace App\Integrations\KudiSms\Entities;

final readonly class SendSms
{
    /**
     * @param  string|array<int, string>  $recipients
     */
    public function __construct(
        public string $token,
        public string $senderId,
        public string|array $recipients,
        public string $message,
        public string $gateway = '2',
    ) {}

    /**
     * @return array{token: string, senderID: string, recipients: string, message: string, gateway: string}
     */
    public function toRequestBody(): array
    {
        $recipientsFormatted = is_array($this->recipients)
            ? implode(',', $this->recipients)
            : $this->recipients;

        return [
            'token' => $this->token,
            'senderID' => $this->senderId,
            'recipients' => $recipientsFormatted,
            'message' => $this->message,
            'gateway' => $this->gateway,
        ];
    }
}
