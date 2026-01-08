<?php

namespace App\Events\Wallet;

use App\Enums\WalletType;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletTransferred
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $sender,
        public User $recipient,
        public float $amount,
        public WalletType $type,
        public ?string $notes = null,
    ) {
    }
}
