<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'payment_type' => $this->payment_type,
            'payment_date' => $this->payment_date,
            'principal_amount' => $this->principal_amount,
            'interest_amount' => $this->interest_amount,
            'balance_after' => $this->balance_after,
        ];
    }
}
