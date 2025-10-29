<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FishCatch extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'user_id', 'species_id', 'zone_id', 'gear_type_id', 'location', 'latitude', 'longitude', 'geo_accuracy_m', 'geohash', 'geo_source', 'caught_at', 'quantity', 'count', 'avg_size_cm', 'vessel_name', 'environmental_data', 'notes', 'flagged', 'flag_reason',
    ];

    protected $casts = [
        'caught_at' => 'datetime',
        'environmental_data' => 'array',
        'notes' => 'array',
        'flagged' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'geo_accuracy_m' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function species()
    {
        return $this->belongsTo(Species::class);
    }

    public function gearType()
    {
        return $this->belongsTo(GearType::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    // Location relation removed; using plain text column 'location'.

    public function feedbacks()
    {
        return $this->hasMany(CatchFeedback::class, 'fish_catch_id');
    }

    // Convenience accessor for decoded weather data (if captured)
    public function getWeatherAttribute(): ?array
    {
        $env = $this->environmental_data;
        if (! is_array($env)) {
            return null;
        }
        if (empty($env['weather_json'])) {
            return null;
        }
        $decoded = json_decode($env['weather_json'], true);

        return is_array($decoded) ? $decoded : null;
    }
}
