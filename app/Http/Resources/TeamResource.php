<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'department'     => new DepartmentResource($this->whenLoaded('department')),
            'department_id'  => $this->department_id,
            'leader'         => new UserResource($this->whenLoaded('leader')),
            'created_by'     => new UserResource($this->whenLoaded('createdBy')),
            'members'        => UserResource::collection(
                $this->whenLoaded('members')
            ),
            'projects'       => ProjectResource::collection(
                $this->whenLoaded('projects')
            ),
            'has_leader'     => $this->hasLeader(),
            'members_count'  => $this->whenCounted('teamMembers'),
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'     => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}