<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'basic_salary',
        'effective_from',
        'effective_to',
        'created_by',
    ];

    protected $casts = [
        'basic_salary'   => 'decimal:2',
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    // =================== Relationships ===================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =================== Scopes ===================

    public function scopeActive($query)
    {
        return $query->whereNull('effective_to');
    }

    public function scopeActiveAt($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            });
    }
}