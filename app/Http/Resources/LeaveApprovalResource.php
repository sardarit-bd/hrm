<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'approver'      => new UserResource($this->whenLoaded('approver')),
            'approver_role' => $this->approver_role,
            'action'        => $this->action,
            'remarks'       => $this->remarks,
            'acted_at'      => $this->acted_at?->format('Y-m-d H:i:s'),
        ];
    }
}