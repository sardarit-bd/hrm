<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'channel'                 => new ChannelResource($this->whenLoaded('channel')),
            'channel_id'              => $this->channel_id,
            'name'                    => $this->name,
            'client_name'             => $this->client_name,
            'description'             => $this->description,
            'project_manager'         => new UserResource($this->whenLoaded('projectManager')),
            'project_manager_id'      => $this->project_manager_id,
            'type'                    => $this->type,
            'status'                  => $this->status,
            'total_budget'            => $this->total_budget,
            'currency'                => $this->currency,
            'exchange_rate_snapshot'  => $this->exchange_rate_snapshot,
            'start_date'              => $this->start_date?->format('Y-m-d'),
            'deadline'                => $this->deadline?->format('Y-m-d'),
            'delivered_date'          => $this->delivered_date?->format('Y-m-d'),
            'is_overdue'              => $this->isOverdue(),
            'teams'                   => TeamResource::collection($this->whenLoaded('teams')),
            'milestones'              => MilestoneResource::collection($this->whenLoaded('milestones')),
            'created_at'              => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'              => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}