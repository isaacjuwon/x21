<?php

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Integrations\Paystack\PaystackConnector;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
class BankListController
{
    #[Response([
        'data' => [
            ['name' => 'GTBank', 'code' => '058', 'slug' => 'guaranty-trust-bank'],
            ['name' => 'Access Bank', 'code' => '044', 'slug' => 'access-bank'],
        ],
    ], status: 200)]
    public function __invoke(PaystackConnector $paystack): JsonResponse
    {
        $banks = $paystack->bank()->list();

        return response()->json([
            'data' => $banks->map(fn ($bank) => [
                'name' => $bank->name,
                'code' => $bank->code,
                'slug' => $bank->slug,
            ])->values(),
        ]);
    }
}
