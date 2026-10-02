<?php

namespace Database\Seeders;

use App\Models\DataPlan;
use Illuminate\Database\Seeder;

class MigrateLegacyDataPlanVtpassCodesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DataPlan::cursor() as $plan) {
            $vtpassCode = $plan->getRawOriginal('vtpass_code');

            if (! empty($vtpassCode)) {
                $plan->providerCodes()->firstOrCreate(
                    ['provider' => 'vtpass'],
                    ['code' => $vtpassCode]
                );
            }
        }
    }
}
