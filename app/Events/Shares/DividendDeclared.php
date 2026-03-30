<?php

namespace App\Events\Shares;

use App\Models\Dividend;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DividendDeclared
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Dividend $dividend) {}
}
