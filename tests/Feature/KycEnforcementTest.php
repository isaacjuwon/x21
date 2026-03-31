<?php

use App\Enums\Kyc\KycStatus;
use App\Enums\Kyc\KycType;
use App\Loans\Specifications\KycRequirementSpecification;
use App\Models\Kyc;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user is not kyc verified without both nin and bvn', function () {
    $user = User::factory()->create();

    // No KYC records
    expect($user->isKycVerified())->toBeFalse();

    // Only NIN verified
    Kyc::create([
        'user_id' => $user->id,
        'type' => KycType::Nin,
        'status' => KycStatus::Verified,
        'number' => '12345678901',
    ]);
    expect($user->isKycVerified())->toBeFalse();

    // Only BVN verified
    Kyc::where('type', KycType::Nin)->delete();
    Kyc::create([
        'user_id' => $user->id,
        'type' => KycType::Bvn,
        'status' => KycStatus::Verified,
        'number' => '12345678901',
    ]);
    expect($user->isKycVerified())->toBeFalse();

    // Both NIN and BVN verified
    Kyc::create([
        'user_id' => $user->id,
        'type' => KycType::Nin,
        'status' => KycStatus::Verified,
        'number' => '12345678901',
    ]);
    expect($user->isKycVerified())->toBeTrue();
});

test('kyc requirement specification requires both nin and bvn', function () {
    $user = User::factory()->create();
    $spec = new KycRequirementSpecification(1000); // Small amount

    // No KYC
    expect($spec->isSatisfiedBy($user))->toBeFalse();

    // Only NIN
    Kyc::create([
        'user_id' => $user->id,
        'type' => KycType::Nin,
        'status' => KycStatus::Verified,
        'number' => '12345678901',
    ]);
    expect($spec->isSatisfiedBy($user))->toBeFalse();

    // Both NIN and BVN
    Kyc::create([
        'user_id' => $user->id,
        'type' => KycType::Bvn,
        'status' => KycStatus::Verified,
        'number' => '12345678901',
    ]);
    expect($spec->isSatisfiedBy($user))->toBeTrue();
});
