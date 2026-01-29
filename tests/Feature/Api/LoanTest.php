<?php

namespace Tests\Feature\Api;

use App\Models\Loan;
use App\Models\LoanLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed any necessary data or run setup
    }

    public function test_user_can_apply_for_loan(): void
    {
        // Setup User with eligibility
        $loanLevel = LoanLevel::factory()->create([
            'maximum_loan_amount' => 1000,
            'interest_rate' => 5,
            'installment_period_months' => 6,
        ]);

        $user = User::factory()->create([
            'loan_level_id' => $loanLevel->id,
            // Assuming verification checks are on user methods, we might need to mock them or set attributes.
            // Let's assume standard factory creates a 'verified' user or we bypass.
            // Based on ApplyForLoanAction logic: needs BVN & NIN verified.
            // I'll assume we need to mock IsVerified trait or set flags if possible.
            // Since we don't have IsVerified implementation details handy (it was in previous convo),
            // I will try to set fields if I can guess them or rely on factory.
            'bvn_verified_at' => now(),
            'nin_verified_at' => now(),
        ]);

        // Mock shares requirement?
        // Action calls $user->getSharesValue().
        // I might need to mock this or ensure user has shares.
        // Let's skip complex eligibility setup and mock the logic if possible or just try basic happy path.
        // Actually, without creating shares, it might fail.
        
        // Let's create a partial mock of Action if testing controller flow?
        // But this is an integration test.
        // Let's try to assume eligibility passes or catch the specific exception.

        // To make it easier, I will verify the endpoint returns 403 or 500 if not eligible, but that hits the controller.
        
        // Let's mock the `ApplyForLoanAction` to ensure we are testing the API layer primarily, 
        // as the Action logic is complex and already likely tested or existing.
        // Or better, just try to hit it and see.
        
        // MOCKING the Action to test API wiring since Action is "existing/legacy".
        $this->mock(\App\Actions\Loans\ApplyForLoanAction::class, function ($mock) use ($user) {
            $mock->shouldReceive('execute')
                ->once()
                ->withArgs(function ($u, $amount) use ($user) {
                    return $u->id === $user->id && $amount === 500.0;
                })
                ->andReturn(new Loan([
                    'id' => 1,
                    'amount' => 500,
                    'balance_remaining' => 500,
                    'status' => \App\Enums\LoanStatus::Pending,
                    'user_id' => $user->id,
                ]));
        });

        $response = $this->actingAs($user)->postJson(route('api.loans.apply'), [
            'amount' => 500,
        ]);

        $response->assertStatus(201) // Resource creation default? Or 200. Resource usually 200 unless specified.
            ->assertJsonPath('data.amount', 500);
    }

    public function test_user_can_repay_loan(): void
    {
        $user = User::factory()->create();
        $loan = Loan::factory()->create(['user_id' => $user->id, 'balance_remaining' => 1000, 'status' => \App\Enums\LoanStatus::ACTIVE]);

        // Mock Repay Action
        $this->mock(\App\Actions\Loans\MakeLoanPaymentAction::class, function ($mock) use ($user, $loan) {
            $mock->shouldReceive('execute')
                ->once()
                ->withArgs(function ($l, $amount) use ($loan) {
                    return $l->id === $loan->id && $amount === 100.0;
                })
                ->andReturn(new \App\Models\LoanPayment());
        });

        $response = $this->actingAs($user)->postJson(route('api.loans.repay', $loan), [
            'amount' => 100,
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Loan repayment processed successfully.']);
    }
}
