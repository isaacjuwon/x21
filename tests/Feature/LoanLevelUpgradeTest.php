<?php

namespace Tests\Feature;

use App\Events\Loans\LoanApplied;
use App\Events\Loans\LoanPaymentMade;
use App\Models\Loan;
use App\Models\LoanLevel;
use App\Models\LoanPayment;
use App\Models\User;
use Database\Seeders\LoanLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LoanLevelUpgradeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LoanLevelSeeder::class);
    }

    public function test_new_user_assigned_basic_level()
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->loan_level_id);
        $this->assertEquals('basic', $user->loanLevel->slug);
    }

    public function test_user_upgraded_after_repayments_threshold()
    {
        Event::fake([
           // LoanApplied::class, // Don't fake this if we want full model interaction, but actually we only need payment event to be listened
        ]);
        
        // Ensure listener is not faked
        Event::assertListening(
            LoanPaymentMade::class,
            \App\Listeners\CheckUserLoanLevelUpgrade::class
        );

        $user = User::factory()->create(); // Starts at Basic (0 repayments)
        $basicLevel = LoanLevel::where('slug', 'basic')->first();
        $silverLevel = LoanLevel::where('slug', 'silver')->first();
        
        // Verify default state
        $this->assertEquals($basicLevel->id, $user->loan_level_id);

        // Create a loan for the user
        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'loan_level_id' => $basicLevel->id,
            'amount' => 1000,
            'status' => 'active',
        ]);

        // Make 4 payments (Silver requires 5)
        for ($i = 0; $i < 4; $i++) {
            $payment = LoanPayment::factory()->create([
                'loan_id' => $loan->id,
                'amount' => 100, 
            ]);
            
            // Dispatch event manually or assume factory/action does it. 
            // Since we are testing integration, let's manually dispatch if the factory doesn't.
            // But better to use the Action if available.
            // For now, let's fire the event manually to test the Listener specifically.
            event(new LoanPaymentMade($loan, $payment, $user));
        }

        // Refresh user
        $user->refresh();
        $this->assertEquals($basicLevel->id, $user->loan_level_id);

        // Make 5th payment
        $payment = LoanPayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 100,
        ]);
        event(new LoanPaymentMade($loan, $payment, $user));

        $user->refresh();
        $this->assertEquals($silverLevel->id, $user->loan_level_id);
    }

    public function test_user_upgraded_to_highest_qualified_lebel()
    {
         $user = User::factory()->create(); 
         $platinumLevel = LoanLevel::where('slug', 'platinum')->first(); // Requires 30
         
         $loan = Loan::factory()->create(['user_id' => $user->id]);

         // Create 30 past payments
         LoanPayment::factory()->count(30)->create(['loan_id' => $loan->id]);

         // Trigger event with the last payment
         $lastPayment = LoanPayment::latest()->first();
         event(new LoanPaymentMade($loan, $lastPayment, $user));

         $user->refresh();
         $this->assertEquals($platinumLevel->id, $user->loan_level_id);
    }
}
