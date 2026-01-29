<?php

declare(strict_types=1);

namespace App\Http\Payloads\Kyc;

final readonly class KycVerificationPayload
{
    public function __construct(
        public string $type,
        public string $idNumber,
        public ?string $dob = null,
        public ?string $phone = null,
        public ?string $documentPath = null,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id_number' => $this->idNumber,
            'dob' => $this->dob,
            'phone' => $this->phone,
            'document_path' => $this->documentPath,
        ];
    }
}
