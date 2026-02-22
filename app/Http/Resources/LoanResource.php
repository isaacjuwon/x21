<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
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
            'balance_remaining' => $this->balance_remaining,
            'status' => $this->status,
            'status_badge' => $this->status_badge,
            'loan_level_name' => $this->loanLevel?->name,
            'interest_rate' => $this->interest_rate,
            'installment_months' => $this->installment_months,
            'monthly_payment' => $this->monthly_payment,
            'total_repayment' => $this->total_repayment,
            'amount_paid' => $this->amount_paid,
            'progress_percentage' => $this->progress_percentage,
            'applied_at' => $this->applied_at,
            'approved_at' => $this->approved_at,
            'disbursed_at' => $this->disbursed_at,
            'due_date' => $this->due_date,
            'next_payment_date' => $this->next_payment_date,
            'loan_level' => $this->whenLoaded('loanLevel'),
            'payments' => LoanPaymentResource::collection($this->whenLoaded('payments')),
        ];

    }
}
