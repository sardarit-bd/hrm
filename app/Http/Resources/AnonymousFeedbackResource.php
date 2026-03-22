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
            'category'   => $this->category,
            'message'    => $this->message,
            'sentiment'  => $this->sentiment,
            'quarter'    => $this->quarter,
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}