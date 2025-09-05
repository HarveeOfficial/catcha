<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatchFeedback extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = [
        'fish_catch_id','expert_id','approved','comments','flags'
    ];

    protected $casts = [
        'flags' => 'array',
        'approved' => 'boolean'
    ];

    public function catch()
    {
        return $this->belongsTo(FishCatch::class, 'fish_catch_id');
    }

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function likes()
    {
        return $this->hasMany(CatchFeedbackLike::class);
    }

    public function getLikesCountAttribute(): int
    {
        return (int) ($this->attributes['likes_count'] ?? $this->likes()->count());
    }
}
