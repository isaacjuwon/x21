<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
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
            'reference' => $this->reference,
            'amount' => (float) $this->amount,
            'formatted_amount' => number_format((float) $this->amount, 2),
            'transaction_type' => $this->transaction_type, // Typically 'increment' or 'decrement'
            'status' => $this->status,
            'notes' => $this->notes,
            'wallet_type' => $this->wallet_type,
            'created_at' => $this->created_at,
        ];
    }
}
