<?php

namespace App\Http\Controllers\Api\V1\Services;

use App\Actions\Vtu\PurchaseElectricityAction;
use App\Enums\Wallets\WalletType;
use App\Http\Requests\Api\V1\Services\PurchaseElectricityRequest;
use App\Http\Resources\Api\V1\Services\TopupTransactionResource;
use App\Models\Brand;
use App\Models\ElectricityPlan;
use App\Models\TopupTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Services', 'VTU and bill payment services')]
#[Authenticated]
class ElectricityController
{
    #[BodyParam('brand_id', 'integer', description: 'Electricity provider brand ID', required: true, example: 2)]
    #[BodyParam('meter_number', 'string', description: 'Meter number (min: 8 chars)', required: true, example: '12345678901')]
    #[BodyParam('meter_type', 'string', description: 'Meter type: Prepaid or Postpaid', required: true, example: 'Prepaid')]
    #[BodyParam('amount', 'number', description: 'Amount to pay (min: 500)', required: true, example: 2000)]
    #[Response([
        'data' => ['id' => 1, 'reference' => 'ELE-XXXXXXXXXX', 'amount' => '2000.00', 'status' => 'completed', 'type' => null, 'response_message' => 'Transaction successful', 'created_at' => '2026-01-01T00:00:00.000000Z'],
    ], status: 201, description: 'Electricity payment initiated')]
    #[Response(['message' => 'Insufficient wallet balance.'], status: 422)]
    #[Response(['message' => 'No active electricity plan found for this provider.'], status: 422)]
    public function __invoke(PurchaseElectricityRequest $request, PurchaseElectricityAction $action): JsonResponse
    {
        $user = $request->user();
        $brand = Brand::findOrFail($request->brand_id);
        $plan = $brand->electricityPlans()->where('status', true)->first();

        if (! $plan) {
            return response()->json(['message' => 'No active electricity plan found for this provider.'], 422);
        }

        if ($user->getWallet(WalletType::General)->available_balance < $request->amount) {
            return response()->json(['message' => 'Insufficient wallet balance.'], 422);
        }

        $transaction = DB::transaction(function () use ($user, $brand, $plan, $request) {
            $topup = TopupTransaction::create([
                'user_id' => $user->id,
                'brand_id' => $brand->id,
                'plan_id' => $plan->id,
                'plan_type' => ElectricityPlan::class,
                'amount' => $request->amount,
                'meter_number' => $request->meter_number,
                'meter_type' => $request->meter_type,
                'status' => 'pending',
                'reference' => 'ELE-'.strtoupper(Str::random(10)),
            ]);

            $user->withdraw(
                amount: (float) $request->amount,
                type: WalletType::General,
                notes: "Electricity Payment: {$brand->name} {$request->meter_type} ({$request->meter_number})",
                transactionable: $topup,
            );

            return $topup;
        });

        $action->handle($transaction);

        return (new TopupTransactionResource($transaction->fresh()))->response()->setStatusCode(201);
    }
}
