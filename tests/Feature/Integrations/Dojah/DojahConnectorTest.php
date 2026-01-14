<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations\Dojah;

use App\Integrations\Dojah\DojahConnector;
use App\Integrations\Dojah\Entities\VerificationRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DojahConnectorTest extends TestCase
{
    public function test_verification_request_payload_is_correct()
    {
        $request = new VerificationRequest(
            firstName: 'John',
            lastName: 'Doe',
            idType: 'nin',
            idNumber: '12345678901',
            dob: '1990-01-01',
            phone: '08012345678',
            email: 'john@example.com',
        );

        $expected = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'id_type' => 'nin',
            'id_number' => '12345678901',
            'dob' => '1990-01-01',
            'phone' => '08012345678',
            'email' => 'john@example.com',
        ];

        $this->assertEquals($expected, $request->toRequestBody());
    }

    public function test_verification_api_call()
    {
        Http::fake([
            'https://api.dojah.io/api/v1/kyc/verify' => Http::response([
                'status' => true,
                'data' => ['verified' => true],
                'message' => 'Verification successful',
            ], 200),
        ]);

        $connector = new DojahConnector(Http::baseUrl('https://api.dojah.io'));
        $resource = $connector->verification();
        $request = new VerificationRequest(
            firstName: 'Jane',
            lastName: 'Smith',
            idType: 'bvn',
            idNumber: '98765432109',
        );
        $response = $resource->verify($request);

        $this->assertTrue($response->success);
        $this->assertEquals(['verified' => true], $response->data);
        $this->assertEquals('Verification successful', $response->message);
    }
}
