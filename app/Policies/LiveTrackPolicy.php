<?php

namespace App\Policies;

use App\Models\LiveTrack;
use App\Models\User;

class LiveTrackPolicy
{
    public function create(?User $user): bool
    {
        return $user !== null; // Only authenticated users can create live tracks
    }
}
