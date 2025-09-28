<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSuggestion extends Model
{
    protected $fillable = [
        'subject_type', 'subject_id', 'scope', 'content', 'model', 'created_by',
    ];
}
