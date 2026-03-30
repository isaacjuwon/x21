<?php

use App\Loans\EligibilityResult;
use App\Loans\LoanEligibilityChecker;
use App\Loans\Specifications\LoanSpecification;
use App\Models\User;

test('returns passing result when all specifications pass', function () {
    $user = Mockery::mock(User::class);

    $spec1 = Mockery::mock(LoanSpecification::class);
    $spec1->shouldReceive('isSatisfiedBy')->with($user)->andReturn(true);

    $spec2 = Mockery::mock(LoanSpecification::class);
    $spec2->shouldReceive('isSatisfiedBy')->with($user)->andReturn(true);

    $checker = new LoanEligibilityChecker([$spec1, $spec2]);
    $result = $checker->check($user);

    expect($result)->toBeInstanceOf(EligibilityResult::class);
    expect($result->passed)->toBeTrue();
    expect($result->failingSpecification)->toBeNull();
});

test('returns failing result with failing spec when one spec fails', function () {
    $user = Mockery::mock(User::class);

    $spec1 = Mockery::mock(LoanSpecification::class);
    $spec1->shouldReceive('isSatisfiedBy')->with($user)->andReturn(true);

    $spec2 = Mockery::mock(LoanSpecification::class);
    $spec2->shouldReceive('isSatisfiedBy')->with($user)->andReturn(false);

    $checker = new LoanEligibilityChecker([$spec1, $spec2]);
    $result = $checker->check($user);

    expect($result->passed)->toBeFalse();
    expect($result->failingSpecification)->toBe($spec2);
});

test('returns first failing spec when multiple specs fail', function () {
    $user = Mockery::mock(User::class);

    $spec1 = Mockery::mock(LoanSpecification::class);
    $spec1->shouldReceive('isSatisfiedBy')->with($user)->andReturn(false);

    $spec2 = Mockery::mock(LoanSpecification::class);
    $spec2->shouldReceive('isSatisfiedBy')->never();

    $checker = new LoanEligibilityChecker([$spec1, $spec2]);
    $result = $checker->check($user);

    expect($result->passed)->toBeFalse();
    expect($result->failingSpecification)->toBe($spec1);
});

test('returns passing result with empty specifications', function () {
    $user = Mockery::mock(User::class);

    $checker = new LoanEligibilityChecker([]);
    $result = $checker->check($user);

    expect($result->passed)->toBeTrue();
    expect($result->failingSpecification)->toBeNull();
});
