<?php

namespace App\Http\Resources\Api\V1\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceBrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'api_code' => $this->api_code,
            'logo' => $this->getFirstMediaUrl('logo') ?: null,
            'status' => $this->status,
            'plans' => ServicePlanResource::collection($this->whenLoaded('plans')),
        ];
    }
}
