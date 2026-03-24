<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'leader' => $this->whenLoaded('leader', fn() => [
                'id'    => $this->leader->id,
                'name'  => $this->leader->full_name,  // full_name, not name
                'email' => $this->leader->email,
            ]),
            'has_leader'    => !is_null($this->leader_id),
            'members' => $this->whenLoaded('members', function () {
                return $this->members->map(fn($user) => [
                    'id'        => $user->id,
                    'name'      => $user->full_name,
                    'email'     => $user->email,
                    'joined_at' => $user->pivot->joined_at,
                ]);
            }),
            'members_count' => $this->whenCounted('teamMembers'),
            'projects'      => $this->whenLoaded('projects'),
            'created_at'    => $this->created_at,
        ];
    }
}