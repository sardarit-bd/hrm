<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'sender_user_id',
        'sender_type',
        'title',
        'message',
        'type',
        'delivery_type',
        'module',
        'entity_type',
        'entity_id',
        'workflow_step',
        'workflow_stage',
        'context',
        'delivered_at',
        'is_read',
        'read_at',
        'created_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'context'    => 'array',
        'read_at'    => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // =================== Relationships ===================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    // =================== Scopes ===================

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBySenderType($query, string $senderType)
    {
        return $query->where('sender_type', $senderType);
    }

    public function scopeByDeliveryType($query, string $deliveryType)
    {
        return $query->where('delivery_type', $deliveryType);
    }

    public function scopeRecent($query, $limit = 20)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // =================== Helpers ===================

    public function isRead(): bool
    {
        return $this->is_read;
    }

    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread(): bool
    {
        return $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }
}
