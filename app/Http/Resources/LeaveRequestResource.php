<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user'        => new UserResource($this->whenLoaded('user')),
            'leave_type'  => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'project'     => new ProjectResource($this->whenLoaded('project')),
            'from_date'   => $this->from_date?->format('Y-m-d'),
            'to_date'     => $this->to_date?->format('Y-m-d'),
            'total_days'  => $this->total_days,
            'reason'      => $this->reason,
            'status'      => $this->status,
            'approvals'   => LeaveApprovalResource::collection(
                $this->whenLoaded('approvals')
            ),
            'pm_approval' => new LeaveApprovalResource(
                $this->whenLoaded('pmApproval')
            ),
            'gm_approval' => new LeaveApprovalResource(
                $this->whenLoaded('gmApproval')
            ),
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'  => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}