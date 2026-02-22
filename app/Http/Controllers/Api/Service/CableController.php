<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Service;

use App\Actions\GenerateReferenceAction;
use App\Actions\Services\CablePurchaseAction;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Service\CablePurchaseRequest;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Brand;
use App\Models\CablePlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CableController extends ApiController
{
    /**
     * Get cable plans.
     */
    public function index(): JsonResponse
    {
        $brands = Brand::with(['cablePlans' => fn ($q) => $q->where('status', true)])
            ->whereHas('cablePlans', fn ($q) => $q->where('status', true))
            ->where('status', true)
            ->get();

        return $this->successResponse($brands);
    }

    /**
     * Purchase cable subscription.
     */
    public function store(
        CablePurchaseRequest $request,
        CablePurchaseAction $action,
        GenerateReferenceAction $generateReference
    ): JsonResponse {
        try {
            return DB::transaction(function () use ($request, $action, $generateReference) {
                $user = $request->user();
                $brand = Brand::find($request->operator_id);
                $plan = CablePlan::find($request->plan_id);
                $reference = $generateReference->handle('CABLE');

                // Debit wallet
                $user->pay((float) $plan->price, "Cable subscription: {$brand->name} {$plan->name} for {$request->smartcard_number}");

                $data = [
                    'operator_name' => $brand->name,
                    'smartcard_number' => $request->smartcard_number,
                    'plan_code' => $plan->vcode ?? $plan->name,
                    'reference' => $reference,
                    'plan_id' => $plan->id,
                    'amount' => $plan->price,
                ];

                $result = $action->handle($data);

                if ($result->isError()) {
                    throw new \Exception($result->error->getMessage());
                }

                return $this->successResponse($result->unwrap(), 'Cable subscription successful.');
            });
        } catch (InsufficientBalanceException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
