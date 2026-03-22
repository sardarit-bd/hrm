<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'start_time'     => $this->start_time,
            'end_time'       => $this->end_time,
            'cross_midnight' => $this->cross_midnight,
            'working_hours'  => $this->working_hours,
            'is_fixed'       => $this->is_fixed,
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'     => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}