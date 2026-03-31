<?php

use App\Actions\Shares\DeclareDividendAction;
use App\Enums\Shares\DividendStatus;
use App\Enums\Wallets\WalletType;
use App\Jobs\ProcessDividendPayoutsJob;
use App\Models\Dividend;
use App\Models\DividendPayout;
use App\Models\ShareHolding;
use App\Models\User;
use App\Settings\ShareSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\assertDatabaseHas;

// Remove RefreshDatabase and use a manual approach for settings migrations
// uses(RefreshDatabase::class);

beforeEach(function () {
    // Run all migrations manually to ensure order
    Artisan::call('migrate:fresh');
    Artisan::call('migrate', [
        '--path' => 'database/settings',
        '--realpath' => true,
    ]);
});

it('calculates and distributes dividends based on percentage of share price', function () {
    // 1. Setup settings
    $settings = app(ShareSettings::class);
    $settings->price_per_share = 10.00;
    $settings->holding_period_days = 30;
    $settings->save();

    // 2. Setup eligible user
    $eligibleUser = User::factory()->create();
    ShareHolding::factory()->create([
        'user_id' => $eligibleUser->id,
        'quantity' => 50,
        'acquired_at' => now()->subDays(31), // Before cutoff
    ]);

    // 3. Setup ineligible user (purchased after cutoff)
    $ineligibleUser = User::factory()->create();
    ShareHolding::factory()->create([
        'user_id' => $ineligibleUser->id,
        'quantity' => 100,
        'acquired_at' => now()->subDays(10), // After cutoff
    ]);

    // 4. Declare dividend (2%)
    $dividendPercentage = 2.0;
    $action = new DeclareDividendAction;
    $dividend = $action->handle($dividendPercentage);

    // Verify dividend record
    expect($dividend->percentage)->toBe($dividendPercentage);
    expect($dividend->share_price)->toBe(10.00);
    expect($dividend->status)->toBe(DividendStatus::Pending);

    // 5. Run distribution job synchronously
    $job = new ProcessDividendPayoutsJob($dividend);
    $job->handle();

    // 6. Verify results
    // Dividend per share = 10 * 0.02 = 0.20
    // Eligible user payout = 0.20 * 50 = 10.00
    // Ineligible user payout = 0.00

    $dividend->refresh();
    expect($dividend->status)->toBe(DividendStatus::Distributed);
    expect($dividend->total_amount)->toBe(10.00);

    // Check eligible user payout
    assertDatabaseHas('dividend_payouts', [
        'dividend_id' => $dividend->id,
        'user_id' => $eligibleUser->id,
        'amount' => 10.00,
    ]);

    $eligibleUserWallet = $eligibleUser->getWallet(WalletType::General);
    expect($eligibleUserWallet->balance)->toBe(10.00);

    // Check ineligible user (should have no payout)
    expect(DividendPayout::where('user_id', $ineligibleUser->id)->count())->toBe(0);
    $ineligibleUserWallet = $ineligibleUser->getWallet(WalletType::General);
    expect($ineligibleUserWallet->balance)->toBe(0.00);
});
