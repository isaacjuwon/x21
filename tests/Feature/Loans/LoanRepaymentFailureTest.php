<?php

namespace Tests\Feature\Loans;

use App\Actions\Loans\MakeLoanPaymentAction;
use App\Events\Loans\LoanRepaymentFailed;
use App\Models\Loan;
use App\Models\User;
use App\Models\Wallet;
use App\Enums\WalletType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LoanRepaymentFailureTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_repayment_failure_reverses_debit(): void
    {
        Event::fake([LoanRepaymentFailed::class]);

        // Create a user with a balance
        $user = User::factory()->create();
        $user->deposit(WalletType::Main, 1000, 'Initial deposit');

        // Create a loan for the user
        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'amount' => 5000,
            'balance' => 5000,
            'currency' => 'NGN',
        ]);

        $amountToPay = 1000;

        // Ensure user has exactly 1000 in Main wallet
        $this->assertEquals(1000, $user->balance(WalletType::Main));

        // Mock the logic after pay() to throw an exception
        // We'll use a partial mock or just mock the action's dependency if any, 
        // but it's easier to mock the action itself or one of its steps.
        // Let's manually trigger the action's failure point.
        
        $action = new class extends MakeLoanPaymentAction {
            public function testExecute(User $user, Loan $loan, float $amount)
            {
                return $this->execute($user, $loan, $amount);
            }

            protected function pay(User $user, float $amount, string $currency): void
            {
                // Simulate successful pay
                $user->decrementBalance(WalletType::Main, $amount, 'Loan Repayment');
            }

            protected function updateLoanBalance(Loan $loan, float $amount): void
            {
                // Simulate failure AFTER pay
                throw new \Exception('Post-debit failure simulation');
            }
        };

        try {
            $action->testExecute($user, $loan, $amountToPay);
        } catch (\Exception $e) {
            $this->assertEquals('Post-debit failure simulation', $e->getMessage());
        }

        // Assert event was dispatched
        Event::assertDispatched(LoanRepaymentFailed::class, function ($event) use ($loan, $amountToPay) {
            return $event->loan->id === $loan->id && $event->amount === (float) $amountToPay;
        });

        // The listener should have been triggered (if not faked, but since we faked it, we manually call it or test listener separately)
        // Let's test the listener logic directly as well or use a bound listener.
        
        $listener = new \App\Listeners\Loans\ReverseLoanRepaymentDebit();
        $event = new LoanRepaymentFailed($loan, $amountToPay, new \Exception('Test'));
        $listener->handle($event);

        // Assert balance is restored
        $user->refresh();
        $this->assertEquals(1000, $user->balance(WalletType::Main));
    }
}
