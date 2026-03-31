<?php

namespace App\Http\Resources\Api\V1\Loans;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'loan_id' => $this->loan_id,
            'instalment_number' => $this->instalment_number,
            'due_date' => $this->due_date,
            'instalment_amount' => $this->instalment_amount,
            'principal_component' => $this->principal_component,
            'interest_component' => $this->interest_component,
            'outstanding_balance' => $this->outstanding_balance,
            'status' => $this->status->value,
            'remaining_amount' => $this->remaining_amount,
            'paid_at' => $this->paid_at,
        ];
    }
}
