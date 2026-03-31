<?php

namespace App\Http\Resources\Api\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->is_admin,
            'loan_level' => $this->whenLoaded('loanLevel', fn () => [
                'id' => $this->loanLevel->id,
                'name' => $this->loanLevel->name,
                'max_amount' => $this->loanLevel->max_amount,
                'interest_rate' => $this->loanLevel->interest_rate,
            ]),
            'share_holding' => $this->whenLoaded('shareHolding', fn () => $this->shareHolding ? [
                'quantity' => $this->shareHolding->quantity,
                'acquired_at' => $this->shareHolding->acquired_at,
            ] : null),
            'created_at' => $this->created_at,
        ];
    }
}
