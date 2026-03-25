<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnonymousFeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'topic_id'   => $this->topic_id,
            'topic'      => $this->whenLoaded('topic', function () {
                return [
                    'id'   => $this->topic->id,
                    'name' => $this->topic->name,
                    'slug' => $this->topic->slug,
                ];
            }),
            'message'    => $this->message,
            'sentiment'  => $this->sentiment,
            'quarter'    => $this->quarter,
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}
