<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseData
{
    public string $network {
        set (string $value) {
            if (blank($value)) {
                throw new \InvalidArgumentException('Network code cannot be blank.');
            }
            $this->network = strtolower(trim($value));
        }
    }

    public string $mobileNumber {
        set (string $value) {
            $this->mobileNumber = preg_replace('/\D/', '', $value);
        }
    }

    public string $dataCode {
        set (string $value) {
            $this->dataCode = trim($value);
        }
    }

    public function __construct(
        string $network,
        string $mobileNumber,
        string $dataCode,
        public readonly ?string $reference = null,
    ) {
        $this->network = $network;
        $this->mobileNumber = $mobileNumber;
        $this->dataCode = $dataCode;
    }

    public function toRequestBody(): array
    {
        return [
            'network' => $this->network,
            'mobile_number' => $this->mobileNumber,
            'data_code' => $this->dataCode,
            'ref' => $this->reference,
        ];
    }
}
