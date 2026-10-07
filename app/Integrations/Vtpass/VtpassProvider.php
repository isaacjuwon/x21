<?php

declare(strict_types=1);

namespace App\Integrations\Vtpass;

use App\Http\Entities\PurchaseAirtime;
use App\Http\Entities\PurchaseCable;
use App\Http\Entities\PurchaseData;
use App\Http\Entities\PurchaseElectricity;
use App\Http\Entities\PurchaseExam;
use App\Http\Entities\ServiceResponse;
use App\Http\Entities\ValidateMeter;
use App\Http\Entities\ValidateSmartcard;
use App\Http\Entities\ValidationResponse;
use App\Integrations\Contracts\Providers\VtuProvider;
use App\Integrations\Vtpass\Resources\PlansResource;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class VtpassProvider implements VtuProvider
{
    public function __construct(
        protected VtpassConnector $connector,
    ) {}

    public function plans(): PlansResource
    {
        return $this->connector->plans();
    }

    public function purchaseAirtime(PurchaseAirtime $entity): ServiceResponse
    {
        $adapted = $this->adaptAirtimeEntity($entity);

        return $this->executeSafely(
            fn () => $this->connector->airtime()->purchase($adapted),
            $entity->reference,
        );
    }

    public function purchaseData(PurchaseData $entity): ServiceResponse
    {
        $adapted = $this->adaptDataEntity($entity);

        return $this->executeSafely(
            fn () => $this->connector->data()->purchase($adapted),
            $entity->reference,
        );
    }

    public function purchaseCable(PurchaseCable $entity): ServiceResponse
    {
        $adapted = $this->adaptCableEntity($entity);

        return $this->executeSafely(
            fn () => $this->connector->cable()->purchase($adapted),
            $entity->reference,
        );
    }

    public function purchaseElectricity(PurchaseElectricity $entity): ServiceResponse
    {
        $adapted = $this->adaptElectricityEntity($entity);

        return $this->executeSafely(
            fn () => $this->connector->electricity()->purchase($adapted),
            $entity->reference,
        );
    }

    public function purchaseExam(PurchaseExam $entity): ServiceResponse
    {
        $adapted = $this->adaptExamEntity($entity);

        return $this->executeSafely(
            fn () => $this->connector->education()->purchase($adapted),
            $entity->reference,
        );
    }

    public function validateSmartcard(ValidateSmartcard $entity): ValidationResponse
    {
        return $this->connector->cable()->validate($entity);
    }

    public function validateMeter(ValidateMeter $entity): ValidationResponse
    {
        return $this->connector->electricity()->validateMeter($entity);
    }

    public function getConnector(): VtpassConnector
    {
        return $this->connector;
    }

    /**
     * Adapt Data entity using the Plan model before request.
     */
    protected function adaptDataEntity(PurchaseData $entity): PurchaseData
    {
        $plan = $this->resolvePlan($entity);

        if ($plan === null) {
            return $entity;
        }

        $code = $this->resolveVtpassCode($plan) ?? $entity->apiCode;
        $network = $plan->brand?->slug ?? $plan->brand?->api_code ?? $entity->network;

        return new PurchaseData(
            network: (string) $network,
            mobileNumber: $entity->mobileNumber,
            apiCode: (string) $code,
            reference: $entity->reference,
            planId: $entity->planId,
            planType: $entity->planType,
        );
    }

    /**
     * Adapt Airtime entity using the Plan model before request.
     */
    protected function adaptAirtimeEntity(PurchaseAirtime $entity): PurchaseAirtime
    {
        $plan = $this->resolvePlan($entity);

        if ($plan === null) {
            return $entity;
        }

        $network = $this->resolveVtpassCode($plan)
            ?? $plan->brand?->slug
            ?? $plan->brand?->api_code
            ?? $entity->network;

        return new PurchaseAirtime(
            network: (string) $network,
            amount: $entity->amount,
            mobileNumber: $entity->mobileNumber,
            portedNumber: $entity->portedNumber,
            reference: $entity->reference,
            planId: $entity->planId,
            planType: $entity->planType,
        );
    }

    /**
     * Adapt Cable entity using the Plan model before request.
     */
    protected function adaptCableEntity(PurchaseCable $entity): PurchaseCable
    {
        $plan = $this->resolvePlan($entity);

        if ($plan === null) {
            return $entity;
        }

        $code = $this->resolveVtpassCode($plan) ?? $entity->apiCode;
        $service = $plan->brand?->slug ?? $plan->brand?->api_code ?? $entity->service;
        $amount = (int) ($plan->price ?? $entity->amount);

        return new PurchaseCable(
            service: (string) $service,
            smartcardNumber: $entity->smartcardNumber,
            apiCode: (string) $code,
            amount: $amount > 0 ? $amount : $entity->amount,
            reference: $entity->reference,
            planId: $entity->planId,
            planType: $entity->planType,
        );
    }

    /**
     * Adapt Electricity entity using the Plan model before request.
     */
    protected function adaptElectricityEntity(PurchaseElectricity $entity): PurchaseElectricity
    {
        $plan = $this->resolvePlan($entity);

        if ($plan === null) {
            return $entity;
        }

        $code = $this->resolveVtpassCode($plan) ?? $entity->apiCode;
        $service = $plan->brand?->slug ?? $plan->brand?->api_code ?? $entity->service;

        return new PurchaseElectricity(
            service: (string) $service,
            meterNumber: $entity->meterNumber,
            meterType: $entity->meterType,
            apiCode: (string) $code,
            amount: $entity->amount,
            reference: $entity->reference,
            planId: $entity->planId,
            planType: $entity->planType,
        );
    }

    /**
     * Adapt Exam entity using the Plan model before request.
     */
    protected function adaptExamEntity(PurchaseExam $entity): PurchaseExam
    {
        $plan = $this->resolvePlan($entity);

        if ($plan === null) {
            return $entity;
        }

        $code = $this->resolveVtpassCode($plan) ?? $entity->apiCode;
        $service = $plan->brand?->slug ?? $plan->brand?->api_code ?? $entity->service;

        return new PurchaseExam(
            service: (string) $service,
            apiCode: (string) $code,
            amount: $entity->amount,
            numberOfPins: $entity->numberOfPins,
            reference: $entity->reference,
            planId: $entity->planId,
            planType: $entity->planType,
        );
    }

    /**
     * Resolve Plan instance from entity metadata.
     */
    protected function resolvePlan(object $entity): ?Model
    {
        $planId = $entity->planId ?? null;
        $planType = $entity->planType ?? null;

        if ($planId === null || $planType === null || ! is_string($planType) || ! is_a($planType, Model::class, true)) {
            return null;
        }

        return $planType::find($planId);
    }

    /**
     * Resolve VTPass provider code from Plan instance.
     */
    protected function resolveVtpassCode(Model $plan): ?string
    {
        if (method_exists($plan, 'apiCodeFor')) {
            $code = $plan->apiCodeFor('vtpass');
            if ($code !== null && $code !== '') {
                return $code;
            }
        }

        if (method_exists($plan, 'resolveApiCode')) {
            $code = $plan->resolveApiCode('vtpass');
            if ($code !== null && $code !== '') {
                return $code;
            }
        }

        return $plan->vtpass_code ?? $plan->api_code ?? null;
    }

    /**
     * Execute upstream request and normalize response according to spec rules:
     * - Definite success (000 / 101) -> 101
     * - Documented terminal errors -> 102
     * - Ambiguous, timeout, 5xx, or unknown -> pending (100)
     */
    protected function executeSafely(callable $callback, ?string $reference = null): ServiceResponse
    {
        try {
            /** @var ServiceResponse $response */
            $response = $callback();

            return $this->normalizeResponse($response, $reference);
        } catch (Throwable $e) {
            // Timeouts, connection errors, and 5xx responses map to pending (never failed).
            return new ServiceResponse(
                code: 100, // Pending
                description: [
                    'ref' => $reference,
                    'status' => 'pending',
                    'response_description' => 'Upstream connection pending: '.$e->getMessage(),
                    'error' => $e->getMessage(),
                ],
            );
        }
    }

    /**
     * Normalize ServiceResponse from VTPass.
     */
    protected function normalizeResponse(ServiceResponse $response, ?string $reference = null): ServiceResponse
    {
        if ($response->isSuccessful()) {
            return $response;
        }

        $raw = $response->description['raw'] ?? [];
        $rawCode = (string) ($raw['code'] ?? '');

        // VTPass '099' / '001' is Transaction Processing / Pending
        if ($rawCode === '099' || $rawCode === '001') {
            return new ServiceResponse(
                code: 100, // Standard pending code
                description: array_merge($response->description, [
                    'status' => 'pending',
                    'response_description' => $response->description['response_description'] ?? 'Transaction Processing',
                ]),
            );
        }

        return $response;
    }
}
