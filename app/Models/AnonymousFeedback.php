<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnonymousFeedback extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'topic_id',
        'message',
        'sentiment',
        'quarter',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'date',
    ];

    // =================== Relationships ===================

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    // =================== Scopes ===================

    public function scopeByTopic($query, int $topicId)
    {
        return $query->where('topic_id', $topicId);
    }

    public function scopeBySentiment($query, string $sentiment)
    {
        return $query->where('sentiment', $sentiment);
    }

    public function scopeByQuarter($query, string $quarter)
    {
        return $query->where('quarter', $quarter);
    }

    public function scopePositive($query)
    {
        return $query->where('sentiment', 'positive');
    }

    public function scopeNeutral($query)
    {
        return $query->where('sentiment', 'neutral');
    }

    public function scopeNegative($query)
    {
        return $query->where('sentiment', 'negative');
    }

    // =================== Helpers ===================

    public function isPositive(): bool
    {
        return $this->sentiment === 'positive';
    }

    public function isNeutral(): bool
    {
        return $this->sentiment === 'neutral';
    }

    public function isNegative(): bool
    {
        return $this->sentiment === 'negative';
    }
}
