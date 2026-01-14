<?php

namespace Tests\Feature\Integrations;

use App\Actions\Kyc\VerificationAction;
use App\Enums\Connectors\KycConnector;
use App\Enums\Kyc\Status as KycStatusEnum;
use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DojahVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_verify_bvn_using_match_endpoint()
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $this->actingAs($user);

        $kyc = KycVerification::factory()->create([
            'user_id' => $user->id,
            'type' => 'bvn',
            'id_number' => '12345678901',
            'status' => KycStatusEnum::Pending,
        ]);

        Http::fake([
            'api.dojah.io/api/v1/kyc/bvn*' => Http::response([
                'entity' => [
                    'bvn' => [
                        'value' => '12345678901',
                        'status' => true,
                    ],
                    'first_name' => [
                        'confidence_value' => 100,
                        'status' => true,
                    ],
                    'last_name' => [
                        'confidence_value' => 100,
                        'status' => true,
                    ],
                ],
            ], 200),
        ]);

        $action = new VerificationAction();
        $result = $action->handle($kyc);

        $this->assertTrue($result->isOk());
        $this->assertEquals(KycStatusEnum::Verified, $kyc->fresh()->status);
        
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.dojah.io/api/v1/kyc/bvn?bvn=12345678901&first_name=John&last_name=Doe'
                && $request->method() === 'GET';
        });
    }

    public function test_it_can_verify_nin_using_lookup_endpoint()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $kyc = KycVerification::factory()->create([
            'user_id' => $user->id,
            'type' => 'nin',
            'id_number' => '12345678901',
            'status' => KycStatusEnum::Pending,
        ]);

        Http::fake([
            'api.dojah.io/api/v1/kyc/nin*' => Http::response([
                'entity' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'gender' => 'Male',
                    'date_of_birth' => '1982-01-01',
                ],
            ], 200),
        ]);

        $action = new VerificationAction();
        $result = $action->handle($kyc);

        $this->assertTrue($result->isOk());
        $this->assertEquals(KycStatusEnum::Verified, $kyc->fresh()->status);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.dojah.io/api/v1/kyc/nin?nin=12345678901'
                && $request->method() === 'GET';
        });
    }
}
