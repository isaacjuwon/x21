<?php

use App\Enums\Loans\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate;

test('successful loan application returns 201 with active status', function () {
    $user = User::factory()->create(['created_at' => now()->subDays(60)]);

    $response = $this->actingAs($user)
        ->withoutMiddleware(Authenticate::class)
        ->postJson('/api/v1/loans', [
            'principal_amount' => 5000,
            'repayment_term_months' => 12,
        ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.status', LoanStatus::Active->value);
    $response->assertJsonPath('data.user_id', $user->id);
});

test('loan is persisted with active status and correct user_id', function () {
    $user = User::factory()->create(['created_at' => now()->subDays(60)]);

    $this->actingAs($user)
        ->withoutMiddleware(Authenticate::class)
        ->postJson('/api/v1/loans', [
            'principal_amount' => 5000,
            'repayment_term_months' => 12,
        ]);

    $this->assertDatabaseHas('loans', [
        'user_id' => $user->id,
        'status' => LoanStatus::Active->value,
        'principal_amount' => 5000,
    ]);
});

test('missing principal_amount returns 422', function () {
    $user = User::factory()->create(['created_at' => now()->subDays(60)]);

    $this->actingAs($user)
        ->withoutMiddleware(Authenticate::class)
        ->postJson('/api/v1/loans', [
            'repayment_term_months' => 12,
        ])->assertStatus(422);
});

test('negative principal_amount returns 422', function () {
    $user = User::factory()->create(['created_at' => now()->subDays(60)]);

    $this->actingAs($user)
        ->withoutMiddleware(Authenticate::class)
        ->postJson('/api/v1/loans', [
            'principal_amount' => -100,
            'repayment_term_months' => 12,
        ])->assertStatus(422);
});

test('missing repayment_term_months returns 422', function () {
    $user = User::factory()->create(['created_at' => now()->subDays(60)]);

    $this->actingAs($user)
        ->withoutMiddleware(Authenticate::class)
        ->postJson('/api/v1/loans', [
            'principal_amount' => 5000,
        ])->assertStatus(422);
});

test('zero repayment_term_months returns 422', function () {
    $user = User::factory()->create(['created_at' => now()->subDays(60)]);

    $this->actingAs($user)
        ->withoutMiddleware(Authenticate::class)
        ->postJson('/api/v1/loans', [
            'principal_amount' => 5000,
            'repayment_term_months' => 0,
        ])->assertStatus(422);
});

test('ineligible user account too new returns 422 without persisting loan', function () {
    $user = User::factory()->create(['created_at' => now()]);

    $this->actingAs($user)
        ->withoutMiddleware(Authenticate::class)
        ->postJson('/api/v1/loans', [
            'principal_amount' => 1000,
            'repayment_term_months' => 12,
        ])->assertStatus(422);

    $this->assertDatabaseMissing('loans', ['user_id' => $user->id]);
});
