<?php

declare(strict_types=1);

use App\Models\Plan;
use App\Services\Vtu\PricingService;

it('calculates selling price with percentage markup and rounding', function () {
    $service = new PricingService;

    // Cost 200, 5% markup = 210, rounded to nearest 5 = 210
    expect($service->calculate(200.00, markupPercentage: 0.05, roundToNearest: 5))->toBe(210.0);

    // Cost 212, 5% markup = 222.6, rounded to nearest 5 = 225
    expect($service->calculate(212.00, markupPercentage: 0.05, roundToNearest: 5))->toBe(225.0);

    // Cost 212, 5% markup = 222.6, rounded to nearest 10 = 230
    expect($service->calculate(212.00, markupPercentage: 0.05, roundToNearest: 10))->toBe(230.0);
});

it('prioritizes custom price over markup', function () {
    $service = new PricingService;

    // Cost 200, custom price 250 -> 250
    expect($service->calculate(200.00, customPrice: 250.00))->toBe(250.0);

    // From plan meta custom_price
    $plan = new Plan(['cost_price' => 150.00, 'meta' => ['custom_price' => 180.00]]);
    expect($service->calculate($plan))->toBe(180.0);
});

it('never allows selling price below cost price', function () {
    $service = new PricingService;

    // Custom price 100 below cost 150 -> guards to 150
    expect($service->calculate(150.00, customPrice: 100.00))->toBe(150.0);
});

it('detects price jumps over 30%', function () {
    $service = new PricingService;

    // 100 to 120 is 20% -> false
    expect($service->isPriceJump(100.00, 120.00))->toBeFalse();

    // 100 to 135 is 35% -> true
    expect($service->isPriceJump(100.00, 135.00))->toBeTrue();

    // 100 to 60 is 40% jump downwards -> true
    expect($service->isPriceJump(100.00, 60.00))->toBeTrue();
});
