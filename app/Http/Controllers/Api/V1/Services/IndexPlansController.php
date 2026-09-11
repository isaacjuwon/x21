<?php

namespace App\Http\Controllers\Api\V1\Services;

use App\Http\Resources\Api\V1\Services\ServiceBrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Services', 'VTU and bill payment services')]
#[Authenticated]
class IndexPlansController
{
    #[Response([
        'data' => [
            'airtime' => [
                [
                    'id' => 1,
                    'name' => 'MTN',
                    'slug' => 'mtn',
                    'api_code' => 'mtn',
                    'logo' => 'https://example.com/mtn.png',
                    'status' => true,
                    'plans' => [],
                ],
            ],
            'data' => [
                [
                    'id' => 1,
                    'name' => 'MTN',
                    'slug' => 'mtn',
                    'api_code' => 'mtn',
                    'logo' => 'https://example.com/mtn.png',
                    'status' => true,
                    'plans' => [
                        ['id' => 3, 'name' => 'MTN 1GB', 'api_code' => 'mtn-1gb', 'type' => 'SME', 'price' => '500.00', 'duration' => '30 days', 'status' => true],
                    ],
                ],
            ],
            'cable' => [],
            'electricity' => [],
            'education' => [],
        ],
    ], status: 200, description: 'All service plans grouped by type')]
    public function __invoke(Request $request): JsonResponse
    {
        // Airtime: client sends brand_id + user-entered amount. No plan selection —
        // the backend auto-picks the active plan. Only brand info is needed.
        $airtimeBrands = Brand::where('status', true)
            ->whereHas('airtimePlans', fn ($q) => $q->where('status', true))
            ->orderBy('name')
            ->get();

        // Electricity: same pattern as airtime — brand_id + user-entered amount.
        $electricityBrands = Brand::where('status', true)
            ->whereHas('electricityPlans', fn ($q) => $q->where('status', true))
            ->orderBy('name')
            ->get();

        // Data, Cable, Education: client sends plan_id. Plans need price so the
        // client can display cost and submit the correct plan_id.
        $brandsWithPlans = fn (string $relation) => Brand::where('status', true)
            ->with([$relation => fn ($q) => $q->where('status', true)->orderBy('name')])
            ->whereHas($relation, fn ($q) => $q->where('status', true))
            ->orderBy('name')
            ->get()
            ->each(fn ($brand) => $brand->setRelation('plans', $brand->$relation))
            ->values();

        return response()->json([
            'data' => [
                'airtime' => ServiceBrandResource::collection($airtimeBrands),
                'data' => ServiceBrandResource::collection($brandsWithPlans('dataPlans')),
                'cable' => ServiceBrandResource::collection($brandsWithPlans('cablePlans')),
                'electricity' => ServiceBrandResource::collection($electricityBrands),
                'education' => ServiceBrandResource::collection($brandsWithPlans('educationPlans')),
            ],
        ]);
    }
}
