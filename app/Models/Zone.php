<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'description',
        'geometry',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'geometry' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function species(): BelongsToMany
    {
        return $this->belongsToMany(Species::class, 'zone_species');
    }
}
