<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'module'     => explode('.', $this->name)[0],
            'action'     => explode('.', $this->name)[1] ?? $this->name,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}