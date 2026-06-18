<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseData
{
    public string $network;

    public string $mobileNumber;

    public string $dataCode;

    public function __construct(
        string $network,
        string $mobileNumber,
        string $dataCode,
        public readonly ?string $reference = null,
    ) {
        if (blank($network)) {
            throw new \InvalidArgumentException('Network code cannot be blank.');
        }

        $this->network = strtolower(trim($network));
        $this->mobileNumber = preg_replace('/\D/', '', $mobileNumber);
        $this->dataCode = trim($dataCode);
    }

    public function toRequestBody(): array
    {
        return [
            'networkId' => $this->network,
            'MobileNumber' => $this->mobileNumber,
            'DataPlan' => $this->dataCode,
            'ref' => $this->reference,
        ];
    }
}
