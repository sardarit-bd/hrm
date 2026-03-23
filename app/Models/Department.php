<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =================== Relationships ===================

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function activeUsers()
    {
        return $this->hasMany(User::class)
            ->where('status', 'active');
    }

    public function activeTeams()
    {
        return $this->hasMany(Team::class);
    }

    // =================== Scopes ===================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // =================== Helpers ===================

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function hasManager(): bool
    {
        return !is_null($this->manager_id);
    }
}