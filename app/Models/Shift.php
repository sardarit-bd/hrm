<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'cross_midnight',
        'working_hours',
        'is_fixed',
    ];

    protected $casts = [
        'cross_midnight' => 'boolean',
        'working_hours'  => 'decimal:2',
        'is_fixed'       => 'boolean',
    ];

    // =================== Relationships ===================

    public function rosterAssignments()
    {
        return $this->hasMany(RosterAssignment::class);
    }

    // =================== Scopes ===================

    public function scopeFixed($query)
    {
        return $query->where('is_fixed', true);
    }

    public function scopeRotating($query)
    {
        return $query->where('is_fixed', false);
    }
}