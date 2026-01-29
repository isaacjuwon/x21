<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_wallet(): void
    {
        $user = User::factory()->create(['balance' => 1000]);

        $response = $this->actingAs($user)->getJson(route('api.wallet.index'));

        $response->assertOk()
            ->assertJsonPath('data.balance', 1000);
    }

    public function test_can_deposit_direct(): void
    {
        $user = User::factory()->create(['balance' => 0]);

        $response = $this->actingAs($user)->postJson(route('api.wallet.deposit'), [
            'amount' => 500,
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Deposit successful.']);
        
        $this->assertEquals(500, $user->fresh()->balance);
    }

    public function test_can_fund_wallet_via_action(): void
    {
        $user = User::factory()->create();

        // MOCK FundWalletAction
        $this->mock(\App\Actions\Wallet\FundWalletAction::class, function ($mock) use ($user) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturn(\App\Support\Result::ok([
                    'authorization_url' => 'https://example.com/pay',
                    'access_code' => 'ACCESS123',
                ]));
        });

        $response = $this->actingAs($user)->postJson(route('api.wallet.fund'), [
            'amount' => 500,
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJsonPath('authorization_url', 'https://example.com/pay');
    }

    public function test_can_withdraw_via_action(): void
    {
        $user = User::factory()->create(['balance' => 1000]);
        
        // MOCK WithdrawWalletAction
        $this->mock(\App\Actions\Wallet\WithdrawWalletAction::class, function ($mock) use ($user) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturn(\App\Support\Result::ok([
                    'message' => 'Withdrawal initiated successfully.',
                ]));
        });

        $response = $this->actingAs($user)->postJson(route('api.wallet.withdraw'), [
            'amount' => 500,
            'account_number' => '1234567890',
            'bank_code' => '057',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Withdrawal initiated successfully.']);
    }

    public function test_can_transfer(): void
    {
        $sender = User::factory()->create(['balance' => 1000, 'phone_number' => '1111111111']);
        
        $this->mock(\App\Actions\Wallet\TransferFundAction::class, function ($mock) use ($sender) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturn(\App\Support\Result::ok());
        });

        $response = $this->actingAs($sender)->postJson(route('api.wallet.transfer'), [
            'amount' => 100,
            'phone_number' => '2222222222',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Transfer successful.']);
    }

    public function test_can_get_banks(): void
    {
        $user = User::factory()->create();

        $this->mock(\App\Actions\Wallet\FetchBanksAction::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturn(collect([
                    ['name' => 'Zenith Bank', 'code' => '057'],
                ]));
        });

        $response = $this->actingAs($user)->getJson(route('api.banks.index'));

        $response->assertOk()
            ->assertJsonPath('data.0.code', '057');
    }
}
