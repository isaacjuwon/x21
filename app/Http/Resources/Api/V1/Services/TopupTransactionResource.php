<?php

namespace App\Http\Resources\Api\V1\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopupTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'amount' => $this->amount,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'type' => $this->type instanceof \BackedEnum ? $this->type->value : $this->type,
            'response_message' => $this->response_message,
            'created_at' => $this->created_at,
        ];
    }
}
