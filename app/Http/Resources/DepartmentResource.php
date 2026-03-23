<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'manager'     => new UserResource($this->whenLoaded('manager')),
            'is_active'   => $this->is_active,
            'teams'       => TeamResource::collection(
                $this->whenLoaded('teams')
            ),
            'teams_count' => $this->whenCounted('teams'),
            'users_count' => $this->whenCounted('users'),
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'  => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}