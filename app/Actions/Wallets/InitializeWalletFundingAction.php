<?php

namespace App\Actions\Wallets;

use App\Enums\Wallets\TransactionStatus;
use App\Enums\Wallets\TransactionType;
use App\Enums\Wallets\WalletType;
use App\Integrations\Paystack\Entities\InitializePayment;
use App\Managers\ApiManager;
use App\Models\User;
use Illuminate\Support\Str;

class InitializeWalletFundingAction
{
    public function __construct(
        protected ApiManager $apiManager,
    ) {}

    /**
     * Initialize a wallet funding request via Paystack.
     *
     * @return string The authorization URL from Paystack.
     */
    public function handle(User $user, float $amount): string
    {
        $reference = 'WFT-'.strtoupper(Str::random(10));

        // Get the general wallet
        $wallet = $user->getWallet(WalletType::General);

        // Create a pending transaction record
        $transaction = $wallet->transactions()->create([
            'amount' => $amount,
            'type' => TransactionType::Deposit,
            'status' => TransactionStatus::Pending,
            'reference' => $reference,
            'notes' => 'Wallet funding via Paystack',
        ]);

        $initializePayment = new InitializePayment(
            email: $user->email,
            amount: $amount,
            reference: $reference,
            callbackUrl: route('wallet.verify', ['reference' => $reference]),
            metadata: [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
            ]
        );

        $response = $this->apiManager->paymentProvider('paystack')->initializePayment($initializePayment);

        return $response->authorizationUrl;
    }
}
