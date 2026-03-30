<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\AirtimePlan;
use App\Models\DataPlan;
use App\Models\CablePlan;
use App\Models\EducationPlan;
use App\Models\ElectricityPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandAndPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vtuBrands = [
            [
                'name' => 'MTN',
                'slug' => 'mtn',
                'api_code' => 'MTN',
                'data_plans' => [
                    ['type' => 'SME', 'api_code' => 'MTN_SME_1GB', 'price' => 250, 'duration' => '30 days'],
                    ['type' => 'Gifting', 'api_code' => 'MTN_GIFT_2GB', 'price' => 500, 'duration' => '30 days'],
                ]
            ],
            [
                'name' => 'Airtel',
                'slug' => 'airtel',
                'api_code' => 'AIRTEL',
                'data_plans' => [
                    ['type' => 'Corporate Gifting', 'api_code' => 'AIRTEL_CG_1GB', 'price' => 280, 'duration' => '30 days'],
                ]
            ],
            [
                'name' => 'Globacom',
                'slug' => 'glo',
                'api_code' => 'GLO',
                'data_plans' => [
                    ['type' => 'Gifting', 'api_code' => 'GLO_GIFT_1GB', 'price' => 240, 'duration' => '30 days'],
                ]
            ],
            [
                'name' => '9mobile',
                'slug' => '9mobile',
                'api_code' => '9MOBILE',
                'data_plans' => [
                    ['type' => 'SME', 'api_code' => '9MOB_SME_1GB', 'price' => 200, 'duration' => '30 days'],
                ]
            ],
        ];

        foreach ($vtuBrands as $brandData) {
            $brand = Brand::firstOrCreate(
                ['slug' => $brandData['slug']],
                [
                    'name' => $brandData['name'],
                    'api_code' => $brandData['api_code'],
                    'status' => true,
                ]
            );

            // Seed Airtime Plan
            AirtimePlan::firstOrCreate(
                ['brand_id' => $brand->id, 'type' => 'VTU'],
                ['api_code' => $brandData['api_code'] . '_VTU', 'status' => true]
            );

            // Seed Data Plans
            if (isset($brandData['data_plans'])) {
                foreach ($brandData['data_plans'] as $plan) {
                    DataPlan::firstOrCreate(
                        ['brand_id' => $brand->id, 'api_code' => $plan['api_code']],
                        [
                            'type' => $plan['type'],
                            'price' => $plan['price'],
                            'duration' => $plan['duration'],
                            'status' => true,
                        ]
                    );
                }
            }
        }

        // Seed Utility Brands
        $utilityBrands = [
            ['name' => 'DSTV', 'slug' => 'dstv', 'type' => 'cable'],
            ['name' => 'GOTV', 'slug' => 'gotv', 'type' => 'cable'],
            ['name' => 'Ikeja Electric', 'slug' => 'ikeja-electric', 'type' => 'electricity'],
            ['name' => 'WAEC', 'slug' => 'waec', 'type' => 'education'],
        ];

        foreach ($utilityBrands as $uBrand) {
            $brand = Brand::firstOrCreate(
                ['slug' => $uBrand['slug']],
                [
                    'name' => $uBrand['name'],
                    'api_code' => strtoupper($uBrand['slug']),
                    'status' => true,
                ]
            );

            if ($uBrand['type'] === 'cable') {
                CablePlan::firstOrCreate(
                    ['brand_id' => $brand->id, 'type' => 'Starter'],
                    ['api_code' => strtoupper($uBrand['slug']) . '_STARTER', 'price' => 2500, 'duration' => '1 month', 'status' => true]
                );
            } elseif ($uBrand['type'] === 'electricity') {
                ElectricityPlan::firstOrCreate(
                    ['brand_id' => $brand->id, 'type' => 'Prepaid'],
                    ['api_code' => strtoupper($uBrand['slug']) . '_PREPAID', 'status' => true]
                );
            } elseif ($uBrand['type'] === 'education') {
                EducationPlan::firstOrCreate(
                    ['brand_id' => $brand->id, 'type' => 'Result Checker'],
                    ['api_code' => strtoupper($uBrand['slug']) . '_CHECKER', 'price' => 3500, 'duration' => 'Lifetime', 'status' => true]
                );
            }
        }
    }
}
