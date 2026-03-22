<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'date'         => $this->date?->format('Y-m-d'),
            'is_recurring' => $this->is_recurring,
            'is_today'     => $this->isToday(),
            'is_upcoming'  => $this->isUpcoming(),
            'created_at'   => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}