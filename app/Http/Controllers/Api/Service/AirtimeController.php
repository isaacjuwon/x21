<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Service;

use App\Actions\GenerateReferenceAction;
use App\Actions\Services\AirtimePurchaseAction;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Service\AirtimePurchaseRequest;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AirtimeController extends ApiController
{
    /**
     * Get airtime brands.
     */
    public function index(): JsonResponse
    {
        $brands = Brand::whereHas('airtimePlans', fn ($q) => $q->where('status', true))
            ->where('status', true)
            ->get();

        return $this->successResponse($brands);
    }

    /**
     * Purchase airtime.
     */
    public function store(
        AirtimePurchaseRequest $request,
        AirtimePurchaseAction $action,
        GenerateReferenceAction $generateReference
    ): JsonResponse {
        try {
            return DB::transaction(function () use ($request, $action, $generateReference) {
                $user = $request->user();
                $brand = Brand::find($request->network_id);
                $plan = $brand->airtimePlans()->where('status', true)->first();
                $reference = $generateReference->handle('AIRTIME');

                // Debit wallet
                $user->pay((float) $request->amount, "Airtime purchase: {$brand->name} for {$request->phone}");

                $data = [
                    'network' => $brand->name,
                    'phone' => $request->phone,
                    'amount' => $request->amount,
                    'reference' => $reference,
                    'ported' => false,
                    'plan_id' => $plan?->id,
                ];

                $result = $action->handle($data);

                if ($result->isError()) {
                    throw new \Exception($result->error->getMessage());
                }

                return $this->successResponse($result->unwrap(), 'Airtime purchase successful.');
            });
        } catch (InsufficientBalanceException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
