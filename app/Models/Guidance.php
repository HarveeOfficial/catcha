<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guidance extends Model
{
    protected $fillable = [
        'species_id','title','content','type','effective_from','effective_to','active','metadata','status','approved_by','approved_at','rejected_reason','created_by'
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'metadata' => 'array',
    'approved_at' => 'datetime',
    'active' => 'boolean'
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function species()
    {
        return $this->belongsTo(Species::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
