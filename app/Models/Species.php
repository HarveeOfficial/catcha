<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Species extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = [
        'common_name','scientific_name','conservation_status','min_size_cm','seasonal_restrictions'
    ];

    protected $casts = [
        'seasonal_restrictions' => 'array'
    ];

    public function catches()
    {
        return $this->hasMany(FishCatch::class);
    }

    public function guidances()
    {
        return $this->hasMany(Guidance::class);
    }
}
