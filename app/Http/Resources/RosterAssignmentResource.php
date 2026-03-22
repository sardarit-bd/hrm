<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RosterAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user'           => new UserResource($this->whenLoaded('user')),
            'shift'          => new ShiftResource($this->whenLoaded('shift')),
            'weekend_days'   => $this->weekend_days,
            'effective_from' => $this->effective_from?->format('Y-m-d'),
            'effective_to'   => $this->effective_to?->format('Y-m-d'),
            'assigned_by'    => new UserResource($this->whenLoaded('assignedBy')),
            'is_active'      => is_null($this->effective_to),
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}