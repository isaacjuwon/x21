<?php

namespace App\Events\Shares;

use App\Models\ShareOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShareOrderApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ShareOrder $order) {}
}
