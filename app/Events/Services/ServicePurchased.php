<?php

namespace App\Events\Services;

use App\Models\Plan;
use App\Models\TopupTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServicePurchased
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ?Plan $plan;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public TopupTransaction $transaction,
        ?Plan $plan = null,
    ) {
        $this->plan = $plan ?? $transaction->plan;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
