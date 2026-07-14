<?php

use App\Enums\Wallets\TransactionType;
use App\Events\Wallets\WalletWithdrawn;
use App\Listeners\Wallets\SendWalletWithdrawnNotificationListener;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\Wallets\WalletWithdrawnNotification;
use Illuminate\Support\Facades\Notification;

test('wallet withdrawal events notify the user with a withdrawal notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'performed_by_user_id' => $user->id,
        'type' => TransactionType::Withdrawal,
    ]);

    $listener = new SendWalletWithdrawnNotificationListener;
    $listener->handle(new WalletWithdrawn($transaction));

    Notification::assertSentTo($user, WalletWithdrawnNotification::class, function (WalletWithdrawnNotification $notification) use ($transaction): bool {
        return $notification->transaction->id === $transaction->id;
    });
});
