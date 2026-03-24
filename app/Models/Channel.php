<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =================== Relationships ===================

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function activeProjects()
    {
        return $this->hasMany(Project::class)
            ->where('status', 'ongoing');
    }

    // =================== Scopes ===================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // =================== Helpers ===================

    public function isActive(): bool
    {
        return $this->is_active;
    }
}