<?php

namespace App\Http\Controllers\Api\V1\Services;

use App\Actions\Vtu\PurchaseEducationAction;
use App\Enums\Topups\TopupType;
use App\Enums\Wallets\WalletType;
use App\Http\Requests\Api\V1\Services\PurchaseEducationRequest;
use App\Http\Resources\Api\V1\Services\TopupTransactionResource;
use App\Models\EducationPlan;
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
class EducationController
{
    #[BodyParam('brand_id', 'integer', description: 'Education provider brand ID', required: true, example: 4)]
    #[BodyParam('plan_id', 'integer', description: 'Education plan ID (exam type)', required: true, example: 10)]
    #[BodyParam('quantity', 'integer', description: 'Number of pins/tokens to purchase (min: 1)', required: true, example: 1)]
    #[Response([
        'data' => ['id' => 1, 'reference' => 'EDU-XXXXXXXXXX', 'amount' => '1500.00', 'status' => 'completed', 'type' => 'education', 'response_message' => 'Transaction successful', 'created_at' => '2026-01-01T00:00:00.000000Z'],
    ], status: 201, description: 'Education pin purchase initiated')]
    #[Response(['message' => 'Insufficient wallet balance.'], status: 422)]
    public function __invoke(PurchaseEducationRequest $request, PurchaseEducationAction $action): JsonResponse
    {
        $user = $request->user();
        $plan = EducationPlan::findOrFail($request->plan_id);
        $totalAmount = $plan->price * $request->quantity;

        if ($user->getWallet(WalletType::General)->available_balance < $totalAmount) {
            return response()->json(['message' => 'Insufficient wallet balance.'], 422);
        }

        $transaction = DB::transaction(function () use ($user, $plan, $request, $totalAmount) {
            $topup = TopupTransaction::create([
                'user_id' => $user->id,
                'brand_id' => $plan->brand_id,
                'plan_id' => $plan->id,
                'plan_type' => EducationPlan::class,
                'type' => TopupType::Education,
                'amount' => $totalAmount,
                'recipient' => (string) $user->id,
                'meta' => ['quantity' => $request->quantity],
                'status' => 'pending',
                'reference' => 'EDU-'.strtoupper(Str::random(10)),
            ]);

            $user->withdraw(
                amount: (float) $totalAmount,
                type: WalletType::General,
                notes: "Education: {$plan->brand->name} {$plan->name} x{$request->quantity}",
                transactionable: $topup,
            );

            return $topup;
        });

        $action->handle($transaction);

        return (new TopupTransactionResource($transaction->fresh()))->response()->setStatusCode(201);
    }
}
