<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GearType extends Model
{
    protected $fillable = ['name', 'local_name', 'description'];

    public function catches()
    {
        return $this->hasMany(FishCatch::class);
    }
}
