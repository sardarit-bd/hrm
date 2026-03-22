<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZkPunchLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'zk_uid',
        'employee_code',
        'state',
        'punch_time',
        'punch_type',
        'device_id',
        'synced_at',
        'is_processed',
    ];

    protected $casts = [
        'punch_time'   => 'datetime',
        'synced_at'    => 'datetime',
        'is_processed' => 'boolean',
        'zk_uid'       => 'integer',
        'state'        => 'integer',
    ];

    // =================== Relationships ===================

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_code', 'employee_code');
    }

    // =================== Scopes ===================

    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }

    public function scopeProcessed($query)
    {
        return $query->where('is_processed', true);
    }

    public function scopeEntry($query)
    {
        return $query->where('punch_type', 'entry');
    }

    public function scopeExit($query)
    {
        return $query->where('punch_type', 'exit');
    }

    public function scopeByDevice($query, $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('punch_time', $date);
    }

    // =================== Helpers ===================

    public function markAsProcessed(): bool
    {
        return $this->update(['is_processed' => true]);
    }
}