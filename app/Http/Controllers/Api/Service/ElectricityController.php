<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Service;

use App\Actions\GenerateReferenceAction;
use App\Actions\Services\ElectricityPurchaseAction;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Service\ElectricityPurchaseRequest;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ElectricityController extends ApiController
{
    /**
     * Get electricity brands.
     */
    public function index(): JsonResponse
    {
        $brands = Brand::whereHas('electricityPlans', fn ($q) => $q->where('status', true))
            ->where('status', true)
            ->get();

        return $this->successResponse($brands);
    }

    /**
     * Purchase electricity.
     */
    public function store(
        ElectricityPurchaseRequest $request,
        ElectricityPurchaseAction $action,
        GenerateReferenceAction $generateReference
    ): JsonResponse {
        try {
            return DB::transaction(function () use ($request, $action, $generateReference) {
                $user = $request->user();
                $brand = Brand::find($request->operator_id);
                $reference = $generateReference->handle('ELECTRICITY');

                // Debit wallet
                $user->pay((float) $request->amount, "Electricity bill: {$brand->name} ({$request->meter_type}) for {$request->meter_number}");

                $data = [
                    'network_name' => $brand->name,
                    'phone' => $request->meter_number,
                    'meter_type' => $request->meter_type,
                    'amount' => $request->amount,
                    'reference' => $reference,
                    'network_id' => $request->operator_id,
                ];

                $result = $action->handle($data);

                if ($result->isError()) {
                    throw new \Exception($result->error->getMessage());
                }

                return $this->successResponse($result->unwrap(), 'Electricity purchase successful.');
            });
        } catch (InsufficientBalanceException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
