<?php

namespace App\Integrations\Contracts\Providers;

use App\Integrations\Epins\Entities\PurchaseAirtime;
use App\Integrations\Epins\Entities\PurchaseCable;
use App\Integrations\Epins\Entities\PurchaseData;
use App\Integrations\Epins\Entities\PurchaseElectricity;
use App\Integrations\Epins\Entities\PurchaseExam;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Epins\Entities\ValidateMeter;
use App\Integrations\Epins\Entities\ValidateSmartcard;
use App\Integrations\Epins\Entities\ValidationResponse;

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
