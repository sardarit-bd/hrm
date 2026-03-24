<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->full_name,
            'email'     => $this->email,
            'joined_at' => $this->pivot->joined_at,
        ];
    }
}