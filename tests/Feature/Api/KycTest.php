<?php

namespace Tests\Feature\Api;

use App\Enums\Kyc\Status as KycStatus;
use App\Enums\Kyc\Type;
use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KycTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_kyc_history(): void
    {
        $user = User::factory()->create();
        KycVerification::create([
            'user_id' => $user->id,
            'type' => Type::Bvn,
            'id_number' => '12345678901',
            'status' => KycStatus::Pending,
        ]);

        $response = $this->actingAs($user)->getJson(route('api.kyc.index'));

        $response->assertOk()
            ->assertJsonPath('data.0.type', 'bvn')
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_can_submit_verification_request(): void
    {
        $user = User::factory()->create();

        // Mocking Settings? Or relying on default 'automatic'.
        // If automatic, it will try to call VerificationAction.
        // We should mock VerificationAction to avoid external API calls.
        
        $this->mock(\App\Actions\Kyc\VerificationAction::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturn(\App\Support\Result::ok(new KycVerification()));
        });

        // We also need the Create Action to work. It uses DB.
        
        $response = $this->actingAs($user)->postJson(route('api.kyc.verify'), [
            'type' => 'bvn',
            'id_number' => '12345678901',
            'dob' => '1990-01-01',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.type', 'bvn');
            
        $this->assertDatabaseHas('kyc_verifications', [
            'user_id' => $user->id,
            'type' => 'bvn',
            'id_number' => '12345678901',
        ]);
    }
}
