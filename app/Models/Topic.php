<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $topic) {
            if (empty($topic->slug)) {
                $topic->slug = Str::slug($topic->name, '_');
            }
        });

        static::updating(function (self $topic) {
            if ($topic->isDirty('name') && !$topic->isDirty('slug')) {
                $topic->slug = Str::slug($topic->name, '_');
            }
        });
    }

    // =================== Relationships ===================

    public function feedbacks()
    {
        return $this->hasMany(AnonymousFeedback::class);
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
