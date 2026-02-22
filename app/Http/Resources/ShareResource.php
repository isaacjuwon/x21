<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $settings = app(\App\Settings\ShareSettings::class);
        $holdingPeriod = $settings->holding_period;
        
        $isMature = $this->status === \App\Enums\ShareStatus::APPROVED && 
                   $this->approved_at?->lte(now()->subDays($holdingPeriod));
        
        $daysUntilMature = $this->approved_at 
            ? max(0, $holdingPeriod - $this->approved_at->diffInDays(now()))
            : null;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'status_label' => $this->status->getLabel(),
            'status_color' => $this->status->getColor(),
            'is_mature' => $isMature,
            'days_until_mature' => $isMature ? 0 : $daysUntilMature,
            'estimated_value' => $this->quantity * $settings->share_price,
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

}
