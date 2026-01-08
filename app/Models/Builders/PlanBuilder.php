<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PlanBuilder extends Builder
{
  public function brands(): PlanBuilder
  {
        return $this->brands;
  }

  public function type($brandId): Collection
  {
    $query = $this->where('brand_id', $brandId)->get();
    return $query->unique('type')->pluck('type')->map(function ($value) {
        // You can modify this closure to create the desired structure for your new collection
        return [
            'id' => $value,
            'name' => $value,
        ];
    });


  }

  public function plans($brandId = null, $type = null): PlanBuilder
  {
    $query = $this;
    if ($brandId) {
        $query = $query->where('brand_id', $brandId);
    }

    if ($type) {
        $query = $query->where('type', $type);
    }

    return $query;
  }

  public function plan($planId): PlanBuilder
  {
    return $this->where('plan_id', $planId);
  }
}

