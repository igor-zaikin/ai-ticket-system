<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,

            'status' => $this->status?->value ?? $this->status,

            'category' => $this->category?->value ?? $this->category,
            'sentiment' => $this->sentiment?->value ?? $this->sentiment,
            'urgency' => $this->urgency?->value ?? $this->urgency,
            'suggested_reply' => $this->suggested_reply,

            'ai_status' => $this->ai_status?->value ?? $this->ai_status,
            'ai_issue' => $this->ai_issue,
            'ai_processed_at' => $this->ai_processed_at?->toISOString(),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
