<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Service;

use App\Actions\GenerateReferenceAction;
use App\Actions\Services\DataPurchaseAction;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Service\DataPurchaseRequest;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Brand;
use App\Models\DataPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DataController extends ApiController
{
    /**
     * Get data plans.
     */
    public function index(): JsonResponse
    {
        $brands = Brand::with(['dataPlans' => fn ($q) => $q->where('status', true)])
            ->whereHas('dataPlans', fn ($q) => $q->where('status', true))
            ->where('status', true)
            ->get();

        return $this->successResponse($brands);
    }

    /**
     * Purchase data.
     */
    public function store(
        DataPurchaseRequest $request,
        DataPurchaseAction $action,
        GenerateReferenceAction $generateReference
    ): JsonResponse {
        try {
            return DB::transaction(function () use ($request, $action, $generateReference) {
                $user = $request->user();
                $brand = Brand::find($request->network_id);
                $plan = DataPlan::find($request->plan_id);
                $reference = $generateReference->handle('DATA');

                // Debit wallet
                $user->pay((float) $plan->price, "Data purchase: {$brand->name} {$plan->name} for {$request->phone}");

                $data = [
                    'network' => $brand->name,
                    'phone' => $request->phone,
                    'amount' => $plan->price,
                    'reference' => $reference,
                    'plan_code' => $plan->code,
                    'ported' => false,
                    'plan_id' => $plan->id,
                ];

                $result = $action->handle($data);

                if ($result->isError()) {
                    throw new \Exception($result->error->getMessage());
                }

                return $this->successResponse($result->unwrap(), 'Data purchase successful.');
            });
        } catch (InsufficientBalanceException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
