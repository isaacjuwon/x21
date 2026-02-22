<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Service;

use App\Actions\GenerateReferenceAction;
use App\Actions\Services\EducationPurchaseAction;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Service\EducationPurchaseRequest;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Brand;
use App\Models\EducationPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EducationController extends ApiController
{
    /**
     * Get education plans.
     */
    public function index(): JsonResponse
    {
        $brands = Brand::with(['educationPlans' => fn ($q) => $q->where('status', true)])
            ->whereHas('educationPlans', fn ($q) => $q->where('status', true))
            ->where('status', true)
            ->get();

        return $this->successResponse($brands);
    }

    /**
     * Purchase education PIN.
     */
    public function store(
        EducationPurchaseRequest $request,
        EducationPurchaseAction $action,
        GenerateReferenceAction $generateReference
    ): JsonResponse {
        try {
            return DB::transaction(function () use ($request, $action, $generateReference) {
                $user = $request->user();
                $brand = Brand::find($request->operator_id);
                $plan = EducationPlan::find($request->plan_id);
                $quantity = $request->quantity ?? 1;
                $totalPrice = (float) $plan->price * $quantity;
                $reference = $generateReference->handle('EDUCATION');

                // Debit wallet
                $user->pay($totalPrice, "Education PIN: {$brand->name} {$plan->name} (x{$quantity})");

                $data = [
                    'operator_name' => $brand->name,
                    'plan_code' => $plan->planCode ?? $plan->id,
                    'plan_id' => $plan->planCode ?? $plan->id,
                    'reference' => $reference,
                    'amount' => $plan->price,
                    'quantity' => $quantity,
                ];

                $result = $action->handle($data);

                if ($result->isError()) {
                    throw new \Exception($result->error->getMessage());
                }

                return $this->successResponse($result->unwrap(), 'Education purchase successful.');
            });
        } catch (InsufficientBalanceException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
