<?php

namespace Database\Seeders;

use App\Enums\Plans\ServiceType;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class MigrateLegacyDataPlanVtpassCodesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Plan::forService(ServiceType::Data)->cursor() as $plan) {
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
