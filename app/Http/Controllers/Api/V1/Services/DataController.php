<?php

namespace App\Http\Controllers\Api\V1\Services;

use App\Actions\Vtu\PurchaseDataAction;
use App\Enums\Wallets\WalletType;
use App\Http\Requests\Api\V1\Services\PurchaseDataRequest;
use App\Http\Resources\Api\V1\Services\TopupTransactionResource;
use App\Models\DataPlan;
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
class DataController
{
    #[BodyParam('brand_id', 'integer', description: 'Network provider brand ID', required: true, example: 1)]
    #[BodyParam('plan_id', 'integer', description: 'Data plan ID', required: true, example: 3)]
    #[BodyParam('phone_number', 'string', description: 'Recipient phone number', required: true, example: '08012345678')]
    #[Response([
        'data' => ['id' => 1, 'reference' => 'DAT-XXXXXXXXXX', 'amount' => '500.00', 'status' => 'completed', 'type' => null, 'response_message' => 'Transaction successful', 'created_at' => '2026-01-01T00:00:00.000000Z'],
    ], status: 201, description: 'Data purchase initiated')]
    #[Response(['message' => 'Insufficient wallet balance.'], status: 422)]
    public function __invoke(PurchaseDataRequest $request, PurchaseDataAction $action): JsonResponse
    {
        $user = $request->user();
        $plan = DataPlan::findOrFail($request->plan_id);

        if ($user->getWallet(WalletType::General)->available_balance < $plan->price) {
            return response()->json(['message' => 'Insufficient wallet balance.'], 422);
        }

        $transaction = DB::transaction(function () use ($user, $plan, $request) {
            $topup = TopupTransaction::create([
                'user_id' => $user->id,
                'brand_id' => $plan->brand_id,
                'plan_id' => $plan->id,
                'plan_type' => DataPlan::class,
                'amount' => $plan->price,
                'phone_number' => $request->phone_number,
                'status' => 'pending',
                'reference' => 'DAT-'.strtoupper(Str::random(10)),
            ]);

            $user->withdraw(
                amount: (float) $plan->price,
                type: WalletType::General,
                notes: "Data Purchase: {$plan->brand->name} {$plan->type} ({$request->phone_number})",
                transactionable: $topup,
            );

            return $topup;
        });

        $action->handle($transaction);

        return (new TopupTransactionResource($transaction->fresh()))->response()->setStatusCode(201);
    }
}
