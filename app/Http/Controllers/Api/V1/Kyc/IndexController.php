<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Kyc;

use App\Http\Resources\Api\V1\Kyc\KycResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('KYC', 'Identity verification')]
#[Authenticated]
final class IndexController
{
    #[Response([
        'data' => [
            ['id' => 1, 'type' => 'nin', 'type_label' => 'NIN', 'number' => '12345678901', 'method' => 'automatic', 'method_label' => 'Automatic', 'status' => 'verified', 'status_label' => 'Verified', 'rejection_reason' => null, 'verified_at' => '2026-01-01T00:00:00.000000Z', 'created_at' => '2026-01-01T00:00:00.000000Z'],
            ['id' => 2, 'type' => 'bvn', 'type_label' => 'BVN', 'number' => '12345678901', 'method' => 'manual', 'method_label' => 'Manual', 'status' => 'pending', 'status_label' => 'Pending', 'rejection_reason' => null, 'verified_at' => null, 'created_at' => '2026-01-01T00:00:00.000000Z'],
        ],
        'meta' => ['is_fully_verified' => false],
    ], status: 200, description: 'KYC records with overall verification status')]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $kycs = $user->kycs()->get();

        return KycResource::collection($kycs)
            ->additional(['meta' => ['is_fully_verified' => $user->isKycVerified()]])
            ->response();
    }
}
