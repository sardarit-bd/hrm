<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendancePolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                            => $this->id,
            'name'                          => $this->name,
            'grace_period_minutes'          => $this->grace_period_minutes,
            'late_count_threshold'          => $this->late_count_threshold,
            'late_threshold_deduction_days' => $this->late_threshold_deduction_days,
            'absent_deduction_per_day'      => $this->absent_deduction_per_day,
            'half_day_threshold_hours'      => $this->half_day_threshold_hours,
            'effective_from'                => $this->effective_from?->format('Y-m-d'),
            'effective_to'                  => $this->effective_to?->format('Y-m-d'),
            'is_active'                     => is_null($this->effective_to),
            'created_by'                    => new UserResource($this->whenLoaded('createdBy')),
            'created_at'                    => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'                    => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}