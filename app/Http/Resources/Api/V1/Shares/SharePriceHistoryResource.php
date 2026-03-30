<?php

namespace App\Http\Resources\Api\V1\Shares;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SharePriceHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'old_price' => $this->old_price,
            'new_price' => $this->new_price,
            'created_at' => $this->created_at,
        ];
    }
}
