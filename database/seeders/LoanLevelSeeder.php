<?php

namespace Database\Seeders;

use App\Models\LoanLevel;
use Illuminate\Database\Seeder;

class LoanLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loanLevels = [
            [
                "name" => "Basic",
                "slug" => "basic",
                "maximum_loan_amount" => 5000.0,
                "installment_period_months" => 12,
                "interest_rate" => 10.0,
                "repayments_required_for_upgrade" => 0,
                "is_active" => true,
            ],
            [
                "name" => "Silver",
                "slug" => "silver",
                "maximum_loan_amount" => 15000.0,
                "installment_period_months" => 24,
                "interest_rate" => 8.0,
                "repayments_required_for_upgrade" => 5,
                "is_active" => true,
            ],
            [
                "name" => "Gold",
                "slug" => "gold",
                "maximum_loan_amount" => 50000.0,
                "installment_period_months" => 36,
                "interest_rate" => 6.0,
                "repayments_required_for_upgrade" => 15,
                "is_active" => true,
            ],
            [
                "name" => "Platinum",
                "slug" => "platinum",
                "maximum_loan_amount" => 100000.0,
                "installment_period_months" => 48,
                "interest_rate" => 5.0,
                "repayments_required_for_upgrade" => 30,
                "is_active" => true,
            ],
        ];

        foreach ($loanLevels as $level) {
            LoanLevel::updateOrCreate(
                ["slug" => $level["slug"]],
                $level,
            );
        }

        $this->command->info("Loan levels seeded successfully!");
    }
}
