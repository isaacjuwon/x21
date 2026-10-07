<?php

declare(strict_types=1);

use App\Models\Plan;
use Illuminate\Support\Facades\Http;

test('vtu:sync-plans command executes and populates plans', function () {
    Http::fake([
        '*/api/v1/fetchservices' => Http::response([
            'status' => true,
            'data' => [
                ['service_id' => 1, 'network_name' => 'MTN'],
            ],
        ], 200),
        '*/api/v1/fetchdataplans' => Http::response([
            'status' => true,
            'data' => [
                'provider_status' => true,
                'data_plans' => [
                    ['code' => 'VTG_10', 'name' => 'MTN 1GB SME', 'price' => 280, 'duration' => '30 Days'],
                ],
            ],
        ], 200),
    ]);

    $this->artisan('vtu:sync-plans', ['--provider' => 'vtugate', '--service' => 'data'])
        ->expectsOutputToContain('Starting VTU Plan synchronization')
        ->expectsOutputToContain('Plan synchronization complete.')
        ->assertSuccessful();

    expect(Plan::count())->toBe(1)
        ->and(Plan::first()->apiCodeFor('vtugate'))->toBe('VTG_10');
});
