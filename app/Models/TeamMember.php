<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'user_id',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'joined_at' => 'date',
        'left_at'   => 'date',
    ];

    // =================== Relationships ===================

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // =================== Scopes ===================

    public function scopeActive($query)
    {
        return $query->whereNull('left_at');
    }

    public function scopeInactive($query)
    {
        return $query->whereNotNull('left_at');
    }

    public function scopeByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // =================== Helpers ===================

    public function isActive(): bool
    {
        return is_null($this->left_at);
    }

    public function leave(): bool
    {
        return $this->update(['left_at' => now()->toDateString()]);
    }
}