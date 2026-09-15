<?php

namespace App\Http\Controllers\Api\V1\Services;

use App\Actions\Vtu\PurchaseCableAction;
use App\Actions\Vtu\ValidateSmartcardAction;
use App\Enums\Topups\TopupType;
use App\Enums\Wallets\WalletType;
use App\Http\Payloads\V1\Services\ValidateSmartcardPayload;
use App\Http\Requests\Api\V1\Services\PurchaseCableTvRequest;
use App\Http\Requests\Api\V1\Services\ValidateSmartcardRequest;
use App\Http\Resources\Api\V1\Services\TopupTransactionResource;
use App\Models\CablePlan;
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
class CableTvController
{
    #[BodyParam('brand_id', 'integer', description: 'Cable TV provider brand ID', required: true, example: 3)]
    #[BodyParam('plan_id', 'integer', description: 'Cable TV plan ID', required: true, example: 7)]
    #[BodyParam('smart_card_number', 'string', description: 'Smart card / IUC number (min: 10 chars)', required: true, example: '1234567890')]
    #[Response([
        'data' => ['id' => 1, 'reference' => 'CAB-XXXXXXXXXX', 'amount' => '3500.00', 'status' => 'completed', 'type' => null, 'response_message' => 'Transaction successful', 'created_at' => '2026-01-01T00:00:00.000000Z'],
    ], status: 201, description: 'Cable TV subscription initiated')]
    #[Response(['message' => 'Insufficient wallet balance.'], status: 422)]
    public function __invoke(PurchaseCableTvRequest $request, PurchaseCableAction $action): JsonResponse
    {
        $user = $request->user();
        $plan = CablePlan::findOrFail($request->plan_id);

        if ($user->getWallet(WalletType::General)->available_balance < $plan->price) {
            return response()->json(['message' => 'Insufficient wallet balance.'], 422);
        }

        $transaction = DB::transaction(function () use ($user, $plan, $request) {
            $topup = TopupTransaction::create([
                'user_id' => $user->id,
                'brand_id' => $plan->brand_id,
                'plan_id' => $plan->id,
                'plan_type' => CablePlan::class,
                'type' => TopupType::Cable,
                'amount' => $plan->price,
                'recipient' => $request->smart_card_number,
                'status' => 'pending',
                'reference' => 'CAB-'.strtoupper(Str::random(10)),
            ]);

            $user->withdraw(
                amount: (float) $plan->price,
                type: WalletType::General,
                notes: "Cable TV: {$plan->brand->name} {$plan->type} ({$request->smart_card_number})",
                transactionable: $topup,
            );

            return $topup;
        });

        $action->handle($transaction);

        return (new TopupTransactionResource($transaction->fresh()))->response()->setStatusCode(201);
    }

    #[BodyParam('brand_id', 'integer', description: 'Cable TV provider brand ID', required: true, example: 3)]
    #[BodyParam('smart_card_number', 'string', description: 'Smart card / IUC number (min: 10 chars)', required: true, example: '1234567890')]
    #[Response([
        'data' => ['code' => 119, 'description' => ['Customer_Name' => 'John Doe', 'Customer_Number' => '1234567890', 'Address' => '123 Main St']],
    ], status: 200, description: 'Smartcard validated successfully')]
    #[Response([
        'data' => ['code' => 118, 'description' => ['message' => 'Invalid smartcard number']],
    ], status: 200, description: 'Smartcard validation failed')]
    public function validateSmartcard(ValidateSmartcardRequest $request, ValidateSmartcardAction $action): JsonResponse
    {
        $user = $request->user();
        $payload = ValidateSmartcardPayload::fromRequest($request->validated());
        $response = $action->handle($payload, $user?->id);

        return response()->json([
            'data' => [
                'code' => $response->code,
                'description' => $response->description,
                'is_valid' => $response->isValid(),
            ],
        ], 200);
    }
}
