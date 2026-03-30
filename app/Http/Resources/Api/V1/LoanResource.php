<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'principal_amount' => $this->principal_amount,
            'outstanding_balance' => $this->outstanding_balance,
            'interest_rate' => $this->interest_rate,
            'repayment_term_months' => $this->repayment_term_months,
            'interest_method' => $this->interest_method?->value,
            'status' => $this->status?->value,
            'disbursed_at' => $this->disbursed_at,
            'eligibility_passed' => $this->eligibility_passed,
            'notes' => $this->notes,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
        ];
    }
}
