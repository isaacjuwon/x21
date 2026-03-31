<?php

namespace App\Http\Resources\Api\V1\Tickets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'message' => $this->message,
            'status' => $this->status->value,
            'status_label' => $this->status->getLabel(),
            'priority' => $this->priority->value,
            'priority_label' => $this->priority->getLabel(),
            'replies_count' => $this->whenCounted('replies'),
            'replies' => TicketReplyResource::collection($this->whenLoaded('replies')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
