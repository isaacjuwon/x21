<?php

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Enums\Wallets\WalletType;
use App\Http\Resources\Api\V1\Wallet\WalletResource;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
class WalletController
{
    #[Response([
        'data' => [
            'id' => 1,
            'type' => 'general',
            'balance' => '5000.00',
            'held_balance' => '200.00',
            'available_balance' => 4800.0,
            'updated_at' => '2026-01-01T00:00:00.000000Z',
        ],
    ], status: 200, description: 'Wallet balance')]
    public function __invoke(Request $request): WalletResource
    {
        return new WalletResource(
            $request->user()->getWallet(WalletType::General)
        );
    }
}
