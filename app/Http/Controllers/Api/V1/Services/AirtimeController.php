<?php

namespace App\Http\Controllers\Api\V1\Services;

use App\Actions\Vtu\PurchaseAirtimeAction;
use App\Enums\Wallets\WalletType;
use App\Http\Requests\Api\V1\Services\PurchaseAirtimeRequest;
use App\Http\Resources\Api\V1\Services\TopupTransactionResource;
use App\Models\AirtimePlan;
use App\Models\Brand;
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
class AirtimeController
{
    #[BodyParam('brand_id', 'integer', description: 'Network provider brand ID', required: true, example: 1)]
    #[BodyParam('phone_number', 'string', description: 'Recipient phone number', required: true, example: '08012345678')]
    #[BodyParam('amount', 'number', description: 'Airtime amount (min: 50)', required: true, example: 200)]
    #[Response([
        'data' => ['id' => 1, 'reference' => 'AIR-XXXXXXXXXX', 'amount' => '200.00', 'status' => 'completed', 'type' => null, 'response_message' => 'Transaction successful', 'created_at' => '2026-01-01T00:00:00.000000Z'],
    ], status: 201, description: 'Airtime purchase initiated')]
    #[Response(['message' => 'Insufficient wallet balance.'], status: 422)]
    #[Response(['message' => 'No active airtime plan found for this brand.'], status: 422)]
    public function __invoke(PurchaseAirtimeRequest $request, PurchaseAirtimeAction $action): JsonResponse
    {
        $user = $request->user();
        $brand = Brand::findOrFail($request->brand_id);
        $plan = $brand->airtimePlans()->where('status', true)->first();

        if (! $plan) {
            return response()->json(['message' => 'No active airtime plan found for this brand.'], 422);
        }

        if ($user->getWallet(WalletType::General)->available_balance < $request->amount) {
            return response()->json(['message' => 'Insufficient wallet balance.'], 422);
        }

        $transaction = DB::transaction(function () use ($user, $brand, $plan, $request) {
            $topup = TopupTransaction::create([
                'user_id' => $user->id,
                'brand_id' => $brand->id,
                'plan_id' => $plan->id,
                'plan_type' => AirtimePlan::class,
                'amount' => $request->amount,
                'phone_number' => $request->phone_number,
                'status' => 'pending',
                'reference' => 'AIR-'.strtoupper(Str::random(10)),
            ]);

            $user->withdraw(
                amount: (float) $request->amount,
                type: WalletType::General,
                notes: "Airtime Purchase: {$brand->name} ({$request->phone_number})",
                transactionable: $topup,
            );

            return $topup;
        });

        $action->handle($transaction);

        return (new TopupTransactionResource($transaction->fresh()))->response()->setStatusCode(201);
    }
}
