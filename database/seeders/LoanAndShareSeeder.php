<?php

namespace Database\Seeders;

use App\Models\LoanLevel;
use App\Models\ShareListing;
use App\Settings\LoanSettings;
use App\Settings\ShareSettings;
use Illuminate\Database\Seeder;

class LoanAndShareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Loan Level Basic
        LoanLevel::firstOrCreate(
            ['name' => 'Basic'],
            [
                'description' => 'Default loan level for all users',
                'min_amount' => 1000,
                'max_amount' => 10000,
                'interest_rate' => 10,
                'max_term_months' => 6,
                'is_active' => true,
            ]
        );

        // Seed Share Listing
        ShareListing::firstOrCreate(
            ['id' => 1],
            [
                'price' => 10.00,
                'total_shares' => 1000000,
                'available_shares' => 1000000,
            ]
        );

        // Optionally seed settings if not already present via migrations
        $loanSettings = app(LoanSettings::class);
        $loanSettings->min_amount = 1000;
        $loanSettings->max_amount = 100000;
        $loanSettings->default_interest_rate = 10;
        $loanSettings->min_account_age_days = 30;
        $loanSettings->auto_approve = false;
        $loanSettings->save();

        $shareSettings = app(ShareSettings::class);
        $shareSettings->price_per_share = 10.00;
        $shareSettings->min_shares_purchase = 100;
        $shareSettings->max_shares_per_user = 10000;
        $shareSettings->holding_period_days = 90;
        $shareSettings->save();
    }
}
