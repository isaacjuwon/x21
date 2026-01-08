<?php

namespace Tests\Feature;

use App\Models\User;
use App\Settings\ShareSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SharePriceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_share_price_setting()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $settings = app(ShareSettings::class);
        
        // Initial state
        $settings->share_price = 10.0;
        $settings->save();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Admin\Settings\Shares::class) // Assuming this is the component path, need to verify
            ->set('share_price', 25.50)
            ->call('save');

        $settings->refresh();
        $this->assertEquals(25.50, $settings->share_price);
    }

    public function test_share_purchase_uses_configured_price()
    {
        $user = User::factory()->create();
        $user->deposit(\App\Enums\WalletType::MAIN, 1000);
        
        $settings = app(ShareSettings::class);
        $settings->share_price = 20.0;
        $settings->save();

        // Using the component path found in previous turns: resources/views/pages/shares/⚡buy.blade.php
        // The component class is anonymous, so we might need to test the action directly or finding the component name if registered.
        // Alternatively, we can test the HasShares trait logic which we modified/verified.
        
        $user->buyShares(10);
        
        // 10 shares * 20.0 price = 200.0 cost
        $this->assertEquals(800, $user->wallet_balance);
        $this->assertEquals(10, $user->shares()->where('currency', 'SHARE')->first()->quantity);
    }

    public function test_share_sale_uses_configured_price()
    {
        $user = User::factory()->create();
        $settings = app(ShareSettings::class);
        $settings->share_price = 15.0;
        $settings->save();

        // Give user shares manually first
        $user->shares()->create([
            'currency' => 'SHARE',
            'quantity' => 20
        ]);

        $user->sellShares(5);

        // 5 shares * 15.0 price = 75.0 value
        $this->assertEquals(75.0, $user->wallet_balance);
        $this->assertEquals(15, $user->shares()->where('currency', 'SHARE')->first()->quantity);
    }

    public function test_get_shares_value_uses_configured_price()
    {
        $user = User::factory()->create();
        $settings = app(ShareSettings::class);
        $settings->share_price = 50.0;
        $settings->save();

        $user->shares()->create([
            'currency' => 'SHARE',
            'quantity' => 10
        ]);

        // 10 shares * 50.0 price = 500.0 value
        $this->assertEquals(500.0, $user->getSharesValue());
    }
}
