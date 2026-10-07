<?php

declare(strict_types=1);

use App\Enums\Plans\ServiceType;
use App\Filament\Resources\Plans\Pages\CreatePlan;
use App\Filament\Resources\Plans\Pages\ListPlans;
use App\Models\Brand;
use App\Models\Plan;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    config()->set('api.providers.failover.providers', ['vtugate', 'vtpass']);

    $adminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($adminRole);
    $this->actingAs($user);
});

test('plan resource list page renders successfully and displays tabs for each service type', function () {
    $brand = Brand::factory()->create(['name' => 'MTN Nigeria']);

    $airtimePlan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Airtime,
        'name' => 'MTN Airtime VTU',
        'price' => 100.00,
        'status' => true,
    ]);

    $dataPlan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Data,
        'name' => 'MTN 1GB Monthly',
        'price' => 280.00,
        'status' => true,
    ]);

    $cablePlan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Cable,
        'name' => 'DSTV Compact',
        'price' => 12500.00,
        'status' => true,
    ]);

    $component = Livewire::test(ListPlans::class);

    $component->assertSuccessful();

    // Verify all tabs exist
    $tabs = $component->instance()->getTabs();
    expect($tabs)->toHaveKey('all')
        ->and($tabs)->toHaveKey(ServiceType::Airtime->value)
        ->and($tabs)->toHaveKey(ServiceType::Data->value)
        ->and($tabs)->toHaveKey(ServiceType::Cable->value)
        ->and($tabs)->toHaveKey(ServiceType::Electricity->value)
        ->and($tabs)->toHaveKey(ServiceType::Education->value)
        ->and($tabs)->toHaveKey(ServiceType::Exam->value);
});

test('filtering by service type tab shows only matching plans', function () {
    $brand = Brand::factory()->create(['name' => 'MTN Nigeria']);

    $airtimePlan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Airtime,
        'name' => 'MTN Airtime VTU',
        'price' => 100.00,
        'status' => true,
    ]);

    $dataPlan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Data,
        'name' => 'MTN 1GB Data',
        'price' => 280.00,
        'status' => true,
    ]);

    // Test active tab filtering
    Livewire::test(ListPlans::class)
        ->set('activeTab', ServiceType::Data->value)
        ->assertCanSeeTableRecords([$dataPlan])
        ->assertCanNotSeeTableRecords([$airtimePlan]);

    Livewire::test(ListPlans::class)
        ->set('activeTab', ServiceType::Airtime->value)
        ->assertCanSeeTableRecords([$airtimePlan])
        ->assertCanNotSeeTableRecords([$dataPlan]);
});

test('plan create page saves plan with provider codes repeater matching airtime plan format', function () {
    $brand = Brand::factory()->create(['name' => 'MTN Nigeria']);

    Livewire::test(CreatePlan::class)
        ->fillForm([
            'service_type' => ServiceType::Data->value,
            'name' => 'MTN 2GB Gifting',
            'brand_id' => $brand->id,
            'type' => 'GIFTING',
            'duration' => '30 Days',
            'price' => 500.00,
            'cost_price' => 460.00,
            'status' => true,
            'providerCodes' => [
                ['provider' => 'vtugate', 'code' => 'VTG_2GB'],
                ['provider' => 'vtpass', 'code' => 'VTP_2GB'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $plan = Plan::where('name', 'MTN 2GB Gifting')->first();

    expect($plan)->not->toBeNull()
        ->and($plan->service_type)->toBe(ServiceType::Data)
        ->and($plan->providerCodes)->toHaveCount(2)
        ->and($plan->apiCodeFor('vtugate'))->toBe('VTG_2GB')
        ->and($plan->apiCodeFor('vtpass'))->toBe('VTP_2GB');
});
