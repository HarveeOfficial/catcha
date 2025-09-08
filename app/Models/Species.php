<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Species extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'common_name', 'filipino_name', 'scientific_name', 'conservation_status', 'min_size_cm', 'seasonal_restrictions',
    ];

    protected $casts = [
        'seasonal_restrictions' => 'array',
    ];

    public function catches()
    {
        return $this->hasMany(FishCatch::class);
    }

    public function guidances()
    {
        return $this->hasMany(Guidance::class);
    }

    /**
     * Determine if the species is currently in season based on its seasonal_restrictions JSON.
     * Supported shapes (any optional):
     *  {"open_months":[1,2,3]} -> Only these calendar months are open.
     *  {"closed_months":[4,5]} -> All months except these are open.
     *  {"windows":[{"start":"03-15","end":"06-30"},{"start":"09-01","end":"10-15"}]} -> Specific date windows (inclusive). Can wrap year (e.g. start 11-01 end 02-15).
     */
    public function isInSeason(?Carbon $date = null): bool
    {
        $date = $date ?: Carbon::now();
        $rules = $this->seasonal_restrictions;
        if (! is_array($rules) || empty($rules)) {
            return true; // No restrictions recorded => treat as open
        }
        $month = (int) $date->format('n');
        if (! empty($rules['open_months']) && is_array($rules['open_months'])) {
            return in_array($month, $rules['open_months']);
        }
        if (! empty($rules['closed_months']) && is_array($rules['closed_months'])) {
            if (in_array($month, $rules['closed_months'])) {
                return false;
            }
        }
        if (! empty($rules['windows']) && is_array($rules['windows'])) {
            $md = $date->format('m-d');
            foreach ($rules['windows'] as $window) {
                if (! is_array($window) || empty($window['start']) || empty($window['end'])) {
                    continue;
                }
                $start = $window['start']; // MM-DD
                $end = $window['end'];
                if ($start === $end && $md === $start) {
                    return true;
                }
                // Handle wrap-around (e.g., 11-01 -> 02-15)
                if ($start > $end) {
                    if ($md >= $start || $md <= $end) {
                        return true;
                    }
                } else { // Normal window
                    if ($md >= $start && $md <= $end) {
                        return true;
                    }
                }
            }

            // If windows defined and none matched -> closed
            return false;
        }

        return true; // Default permissive
    }

    /**
     * Return a brief status array for API responses.
     */
    public function seasonalStatus(?Carbon $date = null): array
    {
        $in = $this->isInSeason($date);

        return [
            'in_season' => $in,
            'label' => $in ? 'in_season' : 'off_season',
        ];
    }
}
