<?php

namespace Tests\Feature\Actions\Kyc;

use App\Actions\Kyc\VerificationAction;
use App\Enums\Connectors\KycConnector;
use App\Enums\Kyc\Status as KycStatusEnum;
use App\Events\Kyc\KycVerificationCompleted;
use App\Events\Kyc\KycVerificationFailed;
use App\Events\Kyc\KycVerificationVerified;
use App\Integrations\Contracts\KycVerificationInterface;
use App\Integrations\Contracts\VerificationReviewInterface;
use App\Integrations\Dojah\Entities\VerificationResponse;
use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class VerificationActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_verified_event_on_success()
    {
        Event::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        $kyc = KycVerification::create([
            'user_id' => $user->id,
            'type' => 'bvn',
            'id_number' => '12345678901',
            'status' => KycStatusEnum::Pending,
        ]);

        // Mock the connector
        $mockVerification = Mockery::mock(\App\Integrations\Dojah\Resources\VerificationResource::class);
        $mockVerification->shouldReceive('verify')
            ->once()
            ->andReturn(new VerificationResponse(
                success: true,
                data: ['valid' => true]
            ));

        $mockConnector = Mockery::mock(\App\Integrations\Dojah\DojahConnector::class);
        $mockConnector->shouldReceive('verification')
            ->andReturn($mockVerification);

        $this->instance(\App\Integrations\Dojah\DojahConnector::class, $mockConnector);

        $action = new VerificationAction();
        $action->handle($kyc);

        Event::assertDispatched(KycVerificationVerified::class);
        Event::assertDispatched(KycVerificationCompleted::class);
        Event::assertNotDispatched(KycVerificationFailed::class);
    }

    public function test_it_dispatches_failed_event_on_failure()
    {
        Event::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        $kyc = KycVerification::create([
            'user_id' => $user->id,
            'type' => 'bvn',
            'id_number' => '12345678901',
            'status' => KycStatusEnum::Pending,
        ]);

         // Mock the connector
         $mockVerification = Mockery::mock(\App\Integrations\Dojah\Resources\VerificationResource::class);
         $mockVerification->shouldReceive('verify')
             ->once()
             ->andReturn(new VerificationResponse(
                 success: false,
                 data: ['valid' => false]
             ));
 
         $mockConnector = Mockery::mock(\App\Integrations\Dojah\DojahConnector::class);
         $mockConnector->shouldReceive('verification')
             ->andReturn($mockVerification);
 
         $this->instance(\App\Integrations\Dojah\DojahConnector::class, $mockConnector);

        $action = new VerificationAction();
        $action->handle($kyc);

        Event::assertDispatched(KycVerificationFailed::class);
        Event::assertDispatched(KycVerificationCompleted::class);
        Event::assertNotDispatched(KycVerificationVerified::class);
    }
}
