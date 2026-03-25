<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'sender_user_id' => $this->sender_user_id,
            'sender_type' => $this->sender_type,
            'sender'     => $this->whenLoaded('sender', function () {
                if (!$this->sender) {
                    return null;
                }

                return [
                    'id'        => $this->sender->id,
                    'full_name' => $this->sender->full_name,
                    'email'     => $this->sender->email,
                ];
            }),
            'title'      => $this->title,
            'message'    => $this->message,
            'type'       => $this->type,
            'delivery_type' => $this->delivery_type,
            'module'     => $this->module,
            'entity_type' => $this->entity_type,
            'entity_id'  => $this->entity_id,
            'workflow_step' => $this->workflow_step,
            'workflow_stage' => $this->workflow_stage,
            'context'    => $this->context,
            'delivered_at' => $this->delivered_at?->format('Y-m-d H:i:s'),
            'is_read'    => $this->is_read,
            'read_at'    => $this->read_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
