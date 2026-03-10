<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $wallets = $this->wallets->map(fn ($wallet) => [
            'type' => $wallet->type,
            'label' => $wallet->type->getLabel(),
            'balance' => (float) $wallet->balance,
            'formatted_balance' => number_format((float) $wallet->balance, 2),
        ]);

        return [
            'total_balance' => (float) $this->getWalletBalance(),
            'formatted_balance' => number_format((float) $this->getWalletBalance(), 2),
            'wallets' => $wallets,
            // Include recent transactions if loaded
            'recent_transactions' => WalletTransactionResource::collection($this->whenLoaded('walletTransactions')),
        ];
    }
}
