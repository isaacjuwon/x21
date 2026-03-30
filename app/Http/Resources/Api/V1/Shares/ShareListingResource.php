<?php

namespace App\Http\Resources\Api\V1\Shares;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'total_shares' => $this->total_shares,
            'available_shares' => $this->available_shares,
            'updated_at' => $this->updated_at,
        ];
    }
}
