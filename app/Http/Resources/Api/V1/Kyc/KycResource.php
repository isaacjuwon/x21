<?php

namespace App\Http\Resources\Api\V1\Kyc;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class KycResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->getLabel(),
            'number' => $this->number,
            'method' => $this->method->value,
            'method_label' => $this->method->getLabel(),
            'status' => $this->status->value,
            'status_label' => $this->status->getLabel(),
            'rejection_reason' => $this->rejection_reason,
            'verified_at' => $this->verified_at,
            'created_at' => $this->created_at,
        ];
    }
}
