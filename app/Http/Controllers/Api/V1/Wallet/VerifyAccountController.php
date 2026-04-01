<?php

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Integrations\Paystack\PaystackConnector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
class VerifyAccountController
{
    #[QueryParam('account_number', 'string', description: 'Bank account number', required: true, example: '0123456789')]
    #[QueryParam('bank_code', 'string', description: 'Paystack bank code', required: true, example: '058')]
    #[Response([
        'data' => ['account_name' => 'John Doe', 'account_number' => '0123456789'],
    ], status: 200)]
    #[Response(['message' => 'Failed to resolve account.'], status: 422)]
    public function __invoke(Request $request, PaystackConnector $paystack): JsonResponse
    {
        $validated = $request->validate([
            'account_number' => ['required', 'string', 'digits:10'],
            'bank_code' => ['required', 'string'],
        ]);

        $account = $paystack->bank()->resolve(
            $validated['account_number'],
            $validated['bank_code'],
        );

        return response()->json([
            'data' => [
                'account_name' => $account->accountName,
                'account_number' => $account->accountNumber,
            ],
        ]);
    }
}
