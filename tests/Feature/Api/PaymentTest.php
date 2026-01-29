<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_verify_payment(): void
    {
        $user = User::factory()->create();
        $reference = 'TRX-123456';

        // Mock VerifyPaymentAction
        // Since we are mocking the action class which contains external API call logic.
        $this->mock(\App\Actions\Payment\VerifyPaymentAction::class, function ($mock) use ($reference) {
            $mock->shouldReceive('handle')
                ->once()
                ->with($reference)
                ->andReturn(\App\Support\Result::ok([
                    'status' => 'success',
                    'message' => 'Payment verified successfully.',
                    'data' => (object)['status' => 'success', 'amount' => 100],
                ]));
        });

        $response = $this->actingAs($user)->getJson(route('api.payment.verify', $reference));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'success');
    }

    public function test_verification_failure(): void
    {
        $user = User::factory()->create();
        $reference = 'INVALID-REF';

        $this->mock(\App\Actions\Payment\VerifyPaymentAction::class, function ($mock) use ($reference) {
            $mock->shouldReceive('handle')
                ->once()
                ->with($reference)
                ->andReturn(\App\Support\Result::error(new \Exception('Payment verification failed details: Verification failed')));
        });

        $response = $this->actingAs($user)->getJson(route('api.payment.verify', $reference));

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error');
    }
}
