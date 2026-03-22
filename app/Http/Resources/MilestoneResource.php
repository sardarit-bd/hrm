<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MilestoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'project'         => new ProjectResource($this->whenLoaded('project')),
            'title'           => $this->title,
            'description'     => $this->description,
            'due_date'        => $this->due_date?->format('Y-m-d'),
            'completion_date' => $this->completion_date?->format('Y-m-d'),
            'milestone_value' => $this->milestone_value,
            'currency'        => $this->currency,
            'status'          => $this->status,
            'is_overdue'      => $this->isOverdue(),
            'created_at'      => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'      => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}