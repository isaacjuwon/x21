<?php

namespace Tests\Feature\Api;

use App\Actions\Services\AirtimePurchaseAction;
use App\Actions\Services\DataPurchaseAction;
use App\Models\Brand;
use App\Models\DataPlan;
use App\Models\User;
use App\Support\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();
        // Create user with some balance
        $this->user = User::factory()->create();
        $this->user->wallets()->create([
            'type' => \App\Enums\WalletType::MAIN,
            'balance' => 5000.0,
        ]);
        
        $this->brand = Brand::create([
            'name' => 'MTN',
            'slug' => 'mtn',
            'status' => true,
        ]);
    }

    public function test_can_purchase_airtime_and_wallet_is_debited(): void
    {
        $initialBalance = $this->user->wallet_balance;
        $amount = 1000;

        $this->mock(AirtimePurchaseAction::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturn(Result::ok(['message' => 'Airtime purchase successful.']));
        });

        $response = $this->actingAs($this->user)->postJson(route('api.services.airtime'), [
            'network_id' => $this->brand->id,
            'amount' => $amount,
            'phone' => '08012345678',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Airtime purchase successful.']);
        
        $this->assertEquals($initialBalance - $amount, $this->user->fresh()->wallet_balance);
    }

    public function test_can_purchase_data(): void
    {
        $plan = DataPlan::create([
            'brand_id' => $this->brand->id,
            'name' => '1GB Data',
            'price' => 500,
            'code' => '1GB',
            'size' => '1GB',
            'type' => 'SME',
            'status' => true,
        ]);

        $this->mock(DataPurchaseAction::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturn(Result::ok(['message' => 'Data purchase successful.']));
        });

        $response = $this->actingAs($this->user)->postJson(route('api.services.data'), [
            'network_id' => $this->brand->id,
            'data_type' => 'SME',
            'plan_id' => $plan->id,
            'phone' => '08012345678',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Data purchase successful.']);
    }

    public function test_insufficient_balance_trigger_validation_error(): void
    {
        // Try to buy 10000 with a 5000 balance
        $response = $this->actingAs($this->user)->postJson(route('api.services.airtime'), [
            'network_id' => $this->brand->id,
            'amount' => 10000,
            'phone' => '08012345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
        
        $this->assertStringContainsString('Insufficient wallet balance', $response->json('errors.amount.0'));
    }

    public function test_airtime_validation_fails_with_missing_data(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('api.services.airtime'), [
            'network_id' => $this->brand->id,
            // amount and phone missing
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'phone']);
    }
}
