<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name','latitude','longitude','area_code','metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function catches()
    {
        return $this->hasMany(FishCatch::class);
    }
}
