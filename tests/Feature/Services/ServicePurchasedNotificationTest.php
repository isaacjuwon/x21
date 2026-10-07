<?php

declare(strict_types=1);

use App\Enums\Topups\TopupTransactionStatus;
use App\Enums\Topups\TopupType;
use App\Events\Services\ServicePurchased;
use App\Listeners\Services\SendServicePurchasedNotificationListener;
use App\Models\Brand;
use App\Models\Plan;
use App\Models\TopupTransaction;
use App\Models\User;
use App\Notifications\Services\ServicePurchasedNotification;
use Illuminate\Support\Facades\Notification;

test('ServicePurchased event and listener notify user with unified plan information', function () {
    Notification::fake();

    $user = User::factory()->create(['name' => 'John Doe']);
    $brand = Brand::factory()->create(['name' => 'MTN']);
    $plan = Plan::factory()->data()->for($brand)->create([
        'name' => 'MTN 2GB Monthly',
        'price' => 1200,
    ]);

    $transaction = TopupTransaction::factory()->for($user)->create([
        'plan_id' => $plan->id,
        'plan_type' => Plan::class,
        'brand_id' => $brand->id,
        'type' => TopupType::Data,
        'amount' => 1200,
        'recipient' => '08012345678',
        'status' => TopupTransactionStatus::Completed,
        'reference' => 'TOP-123456',
    ]);

    $event = new ServicePurchased($transaction, $plan);
    expect($event->plan->id)->toBe($plan->id);

    $listener = new SendServicePurchasedNotificationListener;
    $listener->handle($event);

    Notification::assertSentTo($user, ServicePurchasedNotification::class, function (ServicePurchasedNotification $notification) use ($transaction, $plan): bool {
        return $notification->transaction->id === $transaction->id
            && $notification->plan?->id === $plan->id;
    });
});

test('ServicePurchasedNotification provides accurate mail, sms, and array representations', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);
    $brand = Brand::factory()->create(['name' => 'DSTV']);
    $plan = Plan::factory()->cable()->for($brand)->create([
        'name' => 'DSTV Compact',
        'price' => 10500,
    ]);

    $transaction = TopupTransaction::factory()->for($user)->create([
        'plan_id' => $plan->id,
        'plan_type' => Plan::class,
        'brand_id' => $brand->id,
        'type' => TopupType::Cable,
        'amount' => 10500,
        'recipient' => '1234567890',
        'status' => TopupTransactionStatus::Completed,
        'reference' => 'CABLE-789012',
    ]);

    $notification = new ServicePurchasedNotification($transaction, $plan);

    $arrayData = $notification->toArray($user);
    expect($arrayData['transaction_id'])->toBe($transaction->id)
        ->and($arrayData['plan_id'])->toBe($plan->id)
        ->and($arrayData['plan_name'])->toBe('DSTV Compact');

    $smsText = $notification->toSms($user);
    expect($smsText)->toContain('DSTV Compact')
        ->toContain('1234567890')
        ->toContain('CABLE-789012');

    $mail = $notification->toMail($user);
    expect($mail->viewData['plan']->id)->toBe($plan->id);
});
