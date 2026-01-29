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
            'applied_at' => $this->applied_at,
            'approved_at' => $this->approved_at,
            'disbursed_at' => $this->disbursed_at,
            'due_date' => $this->due_date,
            'loan_level' => $this->whenLoaded('loanLevel'),
        ];
    }
}
