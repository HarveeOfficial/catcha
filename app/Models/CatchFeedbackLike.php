<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatchFeedbackLike extends Model
{
    protected $fillable = ['catch_feedback_id','user_id'];

    public function feedback()
    {
        return $this->belongsTo(CatchFeedback::class, 'catch_feedback_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
