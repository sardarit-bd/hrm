<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'employee_code' => $this->employee_code,
            'full_name'     => $this->full_name,
            'email'         => $this->email,
            'role'          => $this->getRoleNames()->first(),
            'permissions'   => $this->getAllPermissions()->pluck('name'),
            'department'    => new DepartmentResource($this->whenLoaded('department')),
            'department_id' => $this->department_id,
            'designation'   => $this->designation,
            'phone'         => $this->phone,
            'joining_date'  => $this->joining_date?->format('Y-m-d'),
            'status'        => $this->status,
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}