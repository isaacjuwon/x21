<?php

declare(strict_types=1);

namespace App\Http\Entities;

final class PurchaseData
{
    public string $network;

    public string $mobileNumber;

    public string $apiCode;

    public function __construct(
        string $network,
        string $mobileNumber,
        string $apiCode,
        public readonly ?string $reference = null,
        public readonly ?int $planId = null,
        public readonly ?string $planType = null,
    ) {
        if (blank($network)) {
            throw new \InvalidArgumentException('Network code cannot be blank.');
        }

        $this->network = strtolower(trim($network));
        $this->mobileNumber = preg_replace('/\D/', '', $mobileNumber);
        $this->apiCode = trim($apiCode);
    }

    public function toRequestBody(): array
    {
        return [
            'networkId' => $this->network,
            'MobileNumber' => $this->mobileNumber,
            'DataPlan' => $this->apiCode,
            'ref' => $this->reference,
        ];
    }
}
