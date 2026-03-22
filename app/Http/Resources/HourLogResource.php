<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HourLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'project'      => new ProjectResource($this->whenLoaded('project')),
            'user'         => new UserResource($this->whenLoaded('user')),
            'approved_by'  => new UserResource($this->whenLoaded('approvedBy')),
            'log_date'     => $this->log_date?->format('Y-m-d'),
            'hours_logged' => $this->hours_logged,
            'description'  => $this->description,
            'status'       => $this->status,
            'approved_at'  => $this->approved_at?->format('Y-m-d H:i:s'),
            'created_at'   => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'   => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}