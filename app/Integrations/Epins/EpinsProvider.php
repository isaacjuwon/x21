<?php

namespace App\Integrations\Epins;

use App\Integrations\Contracts\Providers\VtuProvider;
use App\Integrations\Epins\Entities\PurchaseAirtime;
use App\Integrations\Epins\Entities\PurchaseCable;
use App\Integrations\Epins\Entities\PurchaseData;
use App\Integrations\Epins\Entities\PurchaseElectricity;
use App\Integrations\Epins\Entities\PurchaseExam;
use App\Integrations\Epins\Entities\ServiceResponse;

class EpinsProvider implements VtuProvider
{
    public function __construct(
        protected EpinsConnector $connector
    ) {}

    public function purchaseAirtime(PurchaseAirtime $entity): ServiceResponse
    {
        return $this->connector->airtime()->purchase($entity);
    }

    public function purchaseData(PurchaseData $entity): ServiceResponse
    {
        return $this->connector->data()->purchase($entity);
    }

    public function purchaseCable(PurchaseCable $entity): ServiceResponse
    {
        return $this->connector->cable()->purchase($entity);
    }

    public function purchaseElectricity(PurchaseElectricity $entity): ServiceResponse
    {
        return $this->connector->electricity()->purchase($entity);
    }

    public function purchaseExam(PurchaseExam $entity): ServiceResponse
    {
        return $this->connector->education()->purchase($entity);
    }
}
