<?php

use App\Models\Brand;
use App\Models\DataPlan;
use App\Models\PlanProviderCode;
use App\Models\User;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    config()->set('api.providers.failover.providers', ['vtugate', 'vtpass']);

    $adminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($adminRole);
    $this->actingAs($user);
});

function repeaterCreateDataPlan(array $planAttrs, array $providerCodesRows): DataPlan
{
    $plan = DataPlan::create(array_merge([
        'api_code' => '',
    ], Arr::except($planAttrs, 'providerCodes')));

    foreach ($providerCodesRows as $row) {
        $plan->providerCodes()->create($row);
    }

    return $plan->load('providerCodes');
}

function repeaterUpdateDataPlan(DataPlan $plan, array $planAttrs, array $providerCodesRows): DataPlan
{
    $plan->update(Arr::except($planAttrs, 'providerCodes'));

    $existingById = $plan->providerCodes->keyBy('id')->all();
    $keepIds = [];

    foreach ($providerCodesRows as $row) {
        if (isset($row['id']) && isset($existingById[$row['id']])) {
            $existingById[$row['id']]->update(Arr::only($row, ['provider', 'code']));
            $keepIds[] = $row['id'];
        } else {
            $new = $plan->providerCodes()->create(Arr::only($row, ['provider', 'code']));
            $keepIds[] = $new->id;
        }
    }

    $plan->providerCodes()
        ->whereNotIn('id', $keepIds)
        ->delete();

    return $plan->fresh('providerCodes');
}

test('create DataPlan with two provider rows (mimics Repeater relationship create) persists both rows via morphMany', function () {
    $brand = Brand::factory()->create();

    $plan = repeaterCreateDataPlan([
        'name' => '1GB Daily Plan',
        'brand_id' => $brand->id,
        'type' => 'SME',
        'duration' => '30 Days',
        'price' => 1000,
        'status' => true,
    ], [
        ['provider' => 'vtugate', 'code' => 'VTG_MTN_1GB'],
        ['provider' => 'vtpass', 'code' => 'mtn-1gb-1000'],
    ]);

    assertDatabaseHas(DataPlan::class, [
        'id' => $plan->id,
        'name' => '1GB Daily Plan',
        'brand_id' => $brand->id,
        'type' => 'SME',
    ]);
    assertDatabaseCount(PlanProviderCode::class, 2);

    expect($plan->providerCodes)->toHaveCount(2);
    expect($plan->apiCodeFor('vtugate'))->toBe('VTG_MTN_1GB');
    expect($plan->apiCodeFor('vtpass'))->toBe('mtn-1gb-1000');
    expect($plan->api_code)->toBe('VTG_MTN_1GB');
    expect($plan->providerCodes[0]->planable_type)->toBe(DataPlan::class);
    expect($plan->providerCodes[0]->planable_id)->toBe($plan->id);
});

test('update DataPlan mimicking Repeater relationship edit: delete vtpass row and update vtugate code persists', function () {
    $brand = Brand::factory()->create();

    $plan = repeaterCreateDataPlan([
        'name' => 'Old Plan',
        'brand_id' => $brand->id,
        'type' => 'CORPORATE',
        'duration' => '7 Days',
        'price' => 500,
        'status' => true,
    ], [
        ['provider' => 'vtugate', 'code' => 'OLD_VTG'],
        ['provider' => 'vtpass', 'code' => 'OLD_VTP'],
    ]);

    $vtugateRowId = $plan->providerCodes->firstWhere('provider', 'vtugate')?->id;
    $vtpassRowId = $plan->providerCodes->firstWhere('provider', 'vtpass')?->id;

    $updated = repeaterUpdateDataPlan($plan, [
        'name' => 'Updated Plan',
    ], [
        ['id' => $vtugateRowId, 'provider' => 'vtugate', 'code' => 'NEW_VTG_2GB'],
    ]);

    expect($updated->name)->toBe('Updated Plan');
    expect($updated->providerCodes)->toHaveCount(1);
    expect($updated->apiCodeFor('vtugate'))->toBe('NEW_VTG_2GB');
    expect($updated->apiCodeFor('vtpass'))->toBeNull();
    expect($updated->api_code)->toBe('NEW_VTG_2GB');

    assertDatabaseHas(PlanProviderCode::class, ['id' => $vtugateRowId, 'code' => 'NEW_VTG_2GB']);
    assertDatabaseMissing(PlanProviderCode::class, ['id' => $vtpassRowId]);
});
