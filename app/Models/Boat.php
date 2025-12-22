<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Boat extends Model
{
    /** @use HasFactory<\Database\Factories\BoatFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'registration_number',
        'name',
        'owner_name',
        'owner_contact',
        'boat_type',
        'length_m',
        'width_m',
        'gross_tonnage',
        'engine_type',
        'engine_horsepower',
        'home_port',
        'psgc_region',
        'psgc_municipality',
        'psgc_barangay',
        'registration_date',
        'expiry_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'expiry_date' => 'date',
            'length_m' => 'decimal:2',
            'width_m' => 'decimal:2',
            'gross_tonnage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired();
    }
}
