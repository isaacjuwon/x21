<?php

namespace App\Integrations\Contracts\Providers;

use App\Http\Entities\PurchaseAirtime;
use App\Http\Entities\PurchaseCable;
use App\Http\Entities\PurchaseData;
use App\Http\Entities\PurchaseElectricity;
use App\Http\Entities\PurchaseExam;
use App\Http\Entities\ServiceResponse;
use App\Http\Entities\ValidateMeter;
use App\Http\Entities\ValidateSmartcard;
use App\Http\Entities\ValidationResponse;

interface VtuProvider
{
    public function purchaseAirtime(PurchaseAirtime $entity): ServiceResponse;

    public function purchaseData(PurchaseData $entity): ServiceResponse;

    public function purchaseCable(PurchaseCable $entity): ServiceResponse;

    public function purchaseElectricity(PurchaseElectricity $entity): ServiceResponse;

    public function purchaseExam(PurchaseExam $entity): ServiceResponse;

    public function validateSmartcard(ValidateSmartcard $entity): ValidationResponse;

    public function validateMeter(ValidateMeter $entity): ValidationResponse;
}
