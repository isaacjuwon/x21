<?php

namespace App\Http\Resources\Api\V1\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicePlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $attributes = $this->resource->getAttributes();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'api_code' => $this->api_code,
            'type' => $this->type,
            // price and duration only exist on Data, Cable, and Education plans
            'price' => $this->when(array_key_exists('price', $attributes), $this->price),
            'duration' => $this->when(array_key_exists('duration', $attributes), $this->duration),
            'status' => $this->status,
        ];
    }
}
