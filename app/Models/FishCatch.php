<?php

namespace App\Models;

use App\Services\ZoneService;
use Illuminate\Database\Eloquent\Model;

class FishCatch extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'user_id', 'species_id', 'zone_id', 'gear_type_id', 'location', 'psgc_region', 'psgc_municipality', 'psgc_barangay', 'latitude', 'longitude', 'geo_accuracy_m', 'geohash', 'geo_source', 'caught_at', 'quantity', 'count', 'avg_size_cm', 'vessel_name', 'bycatch_quantity', 'bycatch_species_ids', 'discard_quantity', 'discard_species_ids', 'discard_reason', 'discard_reason_other', 'environmental_data', 'notes', 'flagged', 'flag_reason',
    ];

    protected $casts = [
        'caught_at' => 'datetime',
        'environmental_data' => 'array',
        'notes' => 'array',
        'bycatch_species_ids' => 'array',
        'discard_species_ids' => 'array',
        'flagged' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'geo_accuracy_m' => 'float',
        'bycatch_quantity' => 'float',
        'discard_quantity' => 'float',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (FishCatch $catch): void {
            if ($catch->latitude !== null && $catch->longitude !== null && $catch->zone_id === null) {
                $zone = app(ZoneService::class)->findZoneForCoordinates($catch->latitude, $catch->longitude);
                if ($zone) {
                    $catch->update(['zone_id' => $zone->id]);
                }
            }
        });
    }

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

    // Convenience accessor for weather data
    public function getWeatherAttribute(): ?string
    {
        $env = $this->environmental_data;
        if (! is_array($env)) {
            return null;
        }
        // Return weather if stored directly
        if (! empty($env['weather'])) {
            return $env['weather'];
        }
        // Legacy: return decoded weather_json if available
        if (empty($env['weather_json'])) {
            return null;
        }
        $decoded = json_decode($env['weather_json'], true);

        return is_array($decoded) ? $decoded : null;
    }
}
