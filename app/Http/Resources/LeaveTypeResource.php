<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'max_days_per_year' => $this->max_days_per_year,
            'is_paid'           => $this->is_paid,
            'created_at'        => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}