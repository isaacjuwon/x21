<?php

use App\Enums\Wallets\WalletType;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function seedGeneralSettings(): void
{
    if (! Schema::hasTable('settings')) {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group');
            $table->string('name');
            $table->boolean('locked')->default(false);
            $table->json('payload');
            $table->timestamps();
            $table->unique(['group', 'name']);
        });
    }

    $settings = [
        'site_name' => 'Ship',
        'site_logo' => null,
        'site_dark_logo' => null,
        'site_favicon' => null,
        'site_dark_favicon' => null,
        'site_description' => null,
        'contact_email' => null,
        'support_email' => null,
        'maintenance_mode' => false,
        'registration_enabled' => true,
        'currency' => 'NGN',
        'timezone' => 'Africa/Lagos',
    ];

    foreach ($settings as $name => $payload) {
        DB::table('settings')->updateOrInsert(
            ['group' => 'general', 'name' => $name],
            [
                'locked' => false,
                'payload' => json_encode($payload),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }
}

test('user initiated wallet transactions default to the wallet owner as performer', function () {
    seedGeneralSettings();

    $user = User::factory()->create();

    $deposit = $user->deposit(500, WalletType::General, 'Wallet funding');
    $withdrawal = $user->withdraw(150, WalletType::General, 'Bill payment');

    expect($deposit->performedByUser)->not->toBeNull()
        ->and($deposit->performedByUser->is($user))->toBeTrue()
        ->and($withdrawal->performedByUser)->not->toBeNull()
        ->and($withdrawal->performedByUser->is($user))->toBeTrue();
});

test('wallet transactions can record another user as the performer', function () {
    seedGeneralSettings();

    $walletOwner = User::factory()->create();
    $admin = User::factory()->create();

    $deposit = $walletOwner->deposit(
        750,
        WalletType::General,
        'Manual admin credit',
        performedByUserId: $admin->id,
    );

    expect($deposit->performedByUser)->not->toBeNull()
        ->and($deposit->performedByUser->is($admin))->toBeTrue();
});
