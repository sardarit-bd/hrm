<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'date',
        'is_recurring',
    ];

    protected $casts = [
        'date'         => 'date',
        'is_recurring' => 'boolean',
    ];

    // =================== Scopes ===================

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeOneTime($query)
    {
        return $query->where('is_recurring', false);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('date', $year);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
            ->orderBy('date');
    }

    // =================== Helpers ===================

    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }

    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    public function isPast(): bool
    {
        return $this->date->isPast();
    }

    public function isUpcoming(): bool
    {
        return $this->date->isFuture();
    }
}