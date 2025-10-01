<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveTrackPoint extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'live_track_id', 'latitude', 'longitude', 'accuracy_m', 'speed_mps', 'bearing_deg', 'recorded_at', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy_m' => 'float',
            'speed_mps' => 'float',
            'bearing_deg' => 'float',
            'recorded_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(LiveTrack::class, 'live_track_id');
    }
}
