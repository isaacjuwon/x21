<?php

namespace App\Integrations\Contracts\Providers;

use App\Integrations\Epins\Entities\PurchaseAirtime;
use App\Integrations\Epins\Entities\PurchaseCable;
use App\Integrations\Epins\Entities\PurchaseData;
use App\Integrations\Epins\Entities\PurchaseElectricity;
use App\Integrations\Epins\Entities\PurchaseExam;
use App\Integrations\Epins\Entities\ServiceResponse;

interface VtuProvider
{
    public function purchaseAirtime(PurchaseAirtime $entity): ServiceResponse;

    public function purchaseData(PurchaseData $entity): ServiceResponse;

    public function purchaseCable(PurchaseCable $entity): ServiceResponse;

    public function purchaseElectricity(PurchaseElectricity $entity): ServiceResponse;

    public function purchaseExam(PurchaseExam $entity): ServiceResponse;
}
