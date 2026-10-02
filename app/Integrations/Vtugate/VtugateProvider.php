<?php

declare(strict_types=1);

namespace App\Integrations\Vtugate;

use App\Integrations\Contracts\Providers\VtuProvider;
use App\Integrations\Epins\Entities\PurchaseAirtime;
use App\Integrations\Epins\Entities\PurchaseCable;
use App\Integrations\Epins\Entities\PurchaseData;
use App\Integrations\Epins\Entities\PurchaseElectricity;
use App\Integrations\Epins\Entities\PurchaseExam;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Epins\Entities\ValidateMeter;
use App\Integrations\Epins\Entities\ValidateSmartcard;
use App\Integrations\Epins\Entities\ValidationResponse;

class VtugateProvider implements VtuProvider
{
    public function __construct(
        protected VtugateConnector $connector,
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

    public function validateSmartcard(ValidateSmartcard $entity): ValidationResponse
    {
        return $this->connector->cable()->validate($entity);
    }

    public function validateMeter(ValidateMeter $entity): ValidationResponse
    {
        return $this->connector->electricity()->validateMeter($entity);
    }

    public function getConnector(): VtugateConnector
    {
        return $this->connector;
    }
}
