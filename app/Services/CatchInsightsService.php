<?php

namespace App\Services;

use App\Models\Species;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CatchInsightsService
{
    private array $monthNames = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];

    /**
     * Generate insights from catch data.
     */
    public function generateInsights($baseQuery): array
    {
        $insights = [];

        // Get monthly species data for trend analysis
        $monthlySpeciesData = $this->getMonthlySpeciesData($baseQuery);
        $monthlyTotals = $this->getMonthlyTotals($baseQuery);
        $yearlyComparison = $this->getYearlyComparison($baseQuery);

        // Generate various insight types
        $insights = array_merge(
            $insights,
            $this->detectSeasonalPatterns($monthlySpeciesData),
            $this->detectSignificantTrends($monthlySpeciesData),
            $this->detectPeakPeriods($monthlyTotals),
            $this->detectYearOverYearChanges($yearlyComparison),
            $this->detectTopPerformers($baseQuery),
            $this->detectEmergingSpecies($monthlySpeciesData)
        );

        // Sort by priority and limit
        usort($insights, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        return array_slice($insights, 0, 8);
    }

    /**
     * Get monthly species data for analysis.
     */
    private function getMonthlySpeciesData($baseQuery): Collection
    {
        $driver = DB::getDriverName();
        $monthExpr = match ($driver) {
            'mysql', 'mariadb' => 'MONTH(caught_at)',
            'pgsql' => 'EXTRACT(MONTH FROM caught_at)',
            'sqlite' => "CAST(strftime('%m', caught_at) AS INTEGER)",
            default => 'MONTH(caught_at)',
        };
        $yearExpr = match ($driver) {
            'mysql', 'mariadb' => 'YEAR(caught_at)',
            'pgsql' => 'EXTRACT(YEAR FROM caught_at)',
            'sqlite' => "CAST(strftime('%Y', caught_at) AS INTEGER)",
            default => 'YEAR(caught_at)',
        };

        return (clone $baseQuery)
            ->whereNotNull('species_id')
            ->selectRaw("{$yearExpr} as year, {$monthExpr} as month, species_id, 
                         COALESCE(SUM(quantity), 0) as qty, 
                         COALESCE(SUM(count), 0) as fish_count,
                         COUNT(*) as catch_count")
            ->groupBy('year', 'month', 'species_id')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Get monthly totals for trend analysis.
     */
    private function getMonthlyTotals($baseQuery): Collection
    {
        $driver = DB::getDriverName();
        $monthExpr = match ($driver) {
            'mysql', 'mariadb' => 'MONTH(caught_at)',
            'pgsql' => 'EXTRACT(MONTH FROM caught_at)',
            'sqlite' => "CAST(strftime('%m', caught_at) AS INTEGER)",
            default => 'MONTH(caught_at)',
        };
        $yearExpr = match ($driver) {
            'mysql', 'mariadb' => 'YEAR(caught_at)',
            'pgsql' => 'EXTRACT(YEAR FROM caught_at)',
            'sqlite' => "CAST(strftime('%Y', caught_at) AS INTEGER)",
            default => 'YEAR(caught_at)',
        };

        return (clone $baseQuery)
            ->selectRaw("{$yearExpr} as year, {$monthExpr} as month, 
                         COALESCE(SUM(quantity), 0) as qty,
                         COUNT(*) as catch_count")
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Get yearly comparison data.
     */
    private function getYearlyComparison($baseQuery): Collection
    {
        $driver = DB::getDriverName();
        $yearExpr = match ($driver) {
            'mysql', 'mariadb' => 'YEAR(caught_at)',
            'pgsql' => 'EXTRACT(YEAR FROM caught_at)',
            'sqlite' => "CAST(strftime('%Y', caught_at) AS INTEGER)",
            default => 'YEAR(caught_at)',
        };

        return (clone $baseQuery)
            ->selectRaw("{$yearExpr} as year, 
                         COALESCE(SUM(quantity), 0) as qty,
                         COUNT(*) as catch_count")
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();
    }

    /**
     * Detect seasonal patterns for species.
     */
    private function detectSeasonalPatterns(Collection $monthlyData): array
    {
        $insights = [];
        $speciesMap = Species::pluck('common_name', 'id')->all();

        // Group by species
        $bySpecies = $monthlyData->groupBy('species_id');

        foreach ($bySpecies as $speciesId => $data) {
            if ($data->count() < 3) {
                continue;
            }

            $speciesName = $speciesMap[$speciesId] ?? 'Unknown Species';

            // Group by month across all years
            $byMonth = $data->groupBy('month');
            $monthlyAverages = [];

            foreach ($byMonth as $month => $monthData) {
                $monthlyAverages[(int) $month] = $monthData->avg('qty');
            }

            if (count($monthlyAverages) < 3) {
                continue;
            }

            // Find peak months (top 25% of averages)
            $sortedMonths = collect($monthlyAverages)->sortDesc();
            $threshold = $sortedMonths->avg() * 1.5;
            $peakMonths = $sortedMonths->filter(fn ($avg) => $avg >= $threshold)->keys()->all();

            if (count($peakMonths) >= 1 && count($peakMonths) <= 4) {
                $monthRange = $this->formatMonthRange($peakMonths);
                $peakQty = round($sortedMonths->first(), 2);

                $insights[] = [
                    'type' => 'seasonal',
                    'icon' => '📅',
                    'color' => 'blue',
                    'title' => "Seasonal Pattern: {$speciesName}",
                    'description' => "{$speciesName} shows peak catches during {$monthRange}, averaging {$peakQty} kg per month in peak periods.",
                    'priority' => 80,
                ];
            }
        }

        return array_slice($insights, 0, 2);
    }

    /**
     * Detect significant month-over-month trends.
     */
    private function detectSignificantTrends(Collection $monthlyData): array
    {
        $insights = [];
        $speciesMap = Species::pluck('common_name', 'id')->all();

        // Get recent months data
        $currentYear = (int) now()->year;
        $currentMonth = (int) now()->month;

        $bySpecies = $monthlyData->groupBy('species_id');

        foreach ($bySpecies as $speciesId => $data) {
            $speciesName = $speciesMap[$speciesId] ?? 'Unknown Species';

            // Get last 3 months for this species
            $recentData = $data
                ->filter(fn ($d) => $d->year == $currentYear || ($d->year == $currentYear - 1 && $d->month >= $currentMonth))
                ->sortByDesc(fn ($d) => $d->year * 100 + $d->month)
                ->take(3)
                ->values();

            if ($recentData->count() < 2) {
                continue;
            }

            $current = $recentData->first();
            $previous = $recentData->get(1);

            if ($previous->qty > 0) {
                $changePercent = (($current->qty - $previous->qty) / $previous->qty) * 100;

                if (abs($changePercent) >= 30) {
                    $currentMonthName = $this->monthNames[(int) $current->month] ?? 'Unknown';
                    $previousMonthName = $this->monthNames[(int) $previous->month] ?? 'Unknown';
                    $direction = $changePercent > 0 ? 'increase' : 'decrease';
                    $icon = $changePercent > 0 ? '📈' : '📉';
                    $color = $changePercent > 0 ? 'green' : 'red';

                    $insights[] = [
                        'type' => 'trend',
                        'icon' => $icon,
                        'color' => $color,
                        'title' => ucfirst($direction)." in {$speciesName}",
                        'description' => "{$speciesName} catches showed a ".abs(round($changePercent))."% {$direction} from {$previousMonthName} to {$currentMonthName} ".($current->year).'.',
                        'priority' => min(95, 60 + abs($changePercent) / 2),
                    ];
                }
            }
        }

        usort($insights, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        return array_slice($insights, 0, 2);
    }

    /**
     * Detect peak periods in overall catches.
     */
    private function detectPeakPeriods(Collection $monthlyTotals): array
    {
        $insights = [];

        if ($monthlyTotals->count() < 3) {
            return $insights;
        }

        // Find the highest month
        $peak = $monthlyTotals->sortByDesc('qty')->first();
        $average = $monthlyTotals->avg('qty');

        if ($peak && $peak->qty > $average * 1.3) {
            $monthName = $this->monthNames[(int) $peak->month] ?? 'Unknown';

            $insights[] = [
                'type' => 'peak',
                'icon' => '🏆',
                'color' => 'amber',
                'title' => "Peak Period: {$monthName} {$peak->year}",
                'description' => "{$monthName} {$peak->year} recorded the highest catches with ".number_format($peak->qty, 2).' kg total, which is '.round((($peak->qty / $average) - 1) * 100).'% above average.',
                'priority' => 75,
            ];
        }

        // Find consecutive growth months
        $sorted = $monthlyTotals->sortBy(fn ($d) => $d->year * 100 + $d->month)->values();
        $growthStreak = 0;
        $streakStart = null;

        for ($i = 1; $i < $sorted->count(); $i++) {
            if ($sorted[$i]->qty > $sorted[$i - 1]->qty) {
                if ($growthStreak === 0) {
                    $streakStart = $sorted[$i - 1];
                }
                $growthStreak++;
            } else {
                if ($growthStreak >= 3) {
                    $startMonth = $this->monthNames[(int) $streakStart->month] ?? '';
                    $endMonth = $this->monthNames[(int) $sorted[$i - 1]->month] ?? '';

                    $insights[] = [
                        'type' => 'growth',
                        'icon' => '🚀',
                        'color' => 'green',
                        'title' => 'Sustained Growth Period',
                        'description' => "Catches increased consistently from {$startMonth} to {$endMonth} {$sorted[$i - 1]->year}, showing {$growthStreak} consecutive months of growth.",
                        'priority' => 70,
                    ];
                }
                $growthStreak = 0;
            }
        }

        return $insights;
    }

    /**
     * Detect year-over-year changes.
     */
    private function detectYearOverYearChanges(Collection $yearlyData): array
    {
        $insights = [];

        if ($yearlyData->count() < 2) {
            return $insights;
        }

        $sorted = $yearlyData->sortByDesc('year')->values();
        $current = $sorted->first();
        $previous = $sorted->get(1);

        if ($previous && $previous->qty > 0) {
            $changePercent = (($current->qty - $previous->qty) / $previous->qty) * 100;

            if (abs($changePercent) >= 10) {
                $direction = $changePercent > 0 ? 'increased' : 'decreased';
                $icon = $changePercent > 0 ? '📊' : '📉';
                $color = $changePercent > 0 ? 'green' : 'orange';

                $insights[] = [
                    'type' => 'yearly',
                    'icon' => $icon,
                    'color' => $color,
                    'title' => "Year-over-Year: {$current->year} vs {$previous->year}",
                    'description' => "Total catches {$direction} by ".abs(round($changePercent))."% in {$current->year} compared to {$previous->year} (".number_format($current->qty, 2).' kg vs '.number_format($previous->qty, 2).' kg).',
                    'priority' => 85,
                ];
            }
        }

        return $insights;
    }

    /**
     * Detect top performing species.
     */
    private function detectTopPerformers($baseQuery): array
    {
        $insights = [];

        $driver = DB::getDriverName();
        $yearExpr = match ($driver) {
            'mysql', 'mariadb' => 'YEAR(caught_at)',
            'pgsql' => 'EXTRACT(YEAR FROM caught_at)',
            'sqlite' => "CAST(strftime('%Y', caught_at) AS INTEGER)",
            default => 'YEAR(caught_at)',
        };

        $currentYear = now()->year;

        // Get top species this year vs last year
        $thisYear = (clone $baseQuery)
            ->whereNotNull('species_id')
            ->whereRaw("{$yearExpr} = ?", [$currentYear])
            ->selectRaw('species_id, COALESCE(SUM(quantity), 0) as qty')
            ->groupBy('species_id')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        $lastYear = (clone $baseQuery)
            ->whereNotNull('species_id')
            ->whereRaw("{$yearExpr} = ?", [$currentYear - 1])
            ->selectRaw('species_id, COALESCE(SUM(quantity), 0) as qty')
            ->groupBy('species_id')
            ->get()
            ->keyBy('species_id');

        $speciesMap = Species::pluck('common_name', 'id')->all();

        foreach ($thisYear as $index => $current) {
            $speciesName = $speciesMap[$current->species_id] ?? 'Unknown';
            $previous = $lastYear->get($current->species_id);

            if ($previous && $previous->qty > 0) {
                $changePercent = (($current->qty - $previous->qty) / $previous->qty) * 100;

                if ($changePercent >= 50 && $index < 3) {
                    $insights[] = [
                        'type' => 'performer',
                        'icon' => '⭐',
                        'color' => 'yellow',
                        'title' => "Rising Star: {$speciesName}",
                        'description' => "{$speciesName} is up ".round($changePercent).'% this year with '.number_format($current->qty, 2).' kg caught, compared to '.number_format($previous->qty, 2).' kg in '.($currentYear - 1).'.',
                        'priority' => 78,
                    ];
                }
            } elseif (! $previous && $current->qty > 100) {
                $insights[] = [
                    'type' => 'new',
                    'icon' => '🆕',
                    'color' => 'purple',
                    'title' => "New Entry: {$speciesName}",
                    'description' => "{$speciesName} is a new addition this year with ".number_format($current->qty, 2).' kg recorded.',
                    'priority' => 65,
                ];
            }
        }

        return array_slice($insights, 0, 1);
    }

    /**
     * Detect emerging species with growing catches.
     */
    private function detectEmergingSpecies(Collection $monthlyData): array
    {
        $insights = [];
        $speciesMap = Species::pluck('common_name', 'id')->all();

        $currentYear = (int) now()->year;
        $currentMonth = (int) now()->month;

        // Group by species
        $bySpecies = $monthlyData->groupBy('species_id');

        foreach ($bySpecies as $speciesId => $data) {
            $speciesName = $speciesMap[$speciesId] ?? 'Unknown Species';

            // Get data sorted by date
            $sorted = $data->sortBy(fn ($d) => $d->year * 100 + $d->month)->values();

            if ($sorted->count() < 4) {
                continue;
            }

            // Check for consistent upward trend in last 4 data points
            $recentFour = $sorted->take(-4)->values();
            $isUpwardTrend = true;

            for ($i = 1; $i < $recentFour->count(); $i++) {
                if ($recentFour[$i]->qty <= $recentFour[$i - 1]->qty) {
                    $isUpwardTrend = false;
                    break;
                }
            }

            if ($isUpwardTrend) {
                $growthRate = $recentFour->first()->qty > 0
                    ? round((($recentFour->last()->qty - $recentFour->first()->qty) / $recentFour->first()->qty) * 100)
                    : 100;

                if ($growthRate >= 25) {
                    $insights[] = [
                        'type' => 'emerging',
                        'icon' => '🌱',
                        'color' => 'teal',
                        'title' => "Emerging Trend: {$speciesName}",
                        'description' => "{$speciesName} has shown consistent growth over the last 4 months with a {$growthRate}% increase overall.",
                        'priority' => 72,
                    ];
                }
            }
        }

        return array_slice($insights, 0, 1);
    }

    /**
     * Format month range for display.
     */
    private function formatMonthRange(array $months): string
    {
        if (count($months) === 1) {
            return $this->monthNames[$months[0]] ?? 'Unknown';
        }

        sort($months);

        // Check if consecutive
        $isConsecutive = true;
        for ($i = 1; $i < count($months); $i++) {
            $expected = $months[$i - 1] + 1;
            if ($expected > 12) {
                $expected = 1;
            }
            if ($months[$i] !== $expected) {
                $isConsecutive = false;
                break;
            }
        }

        if ($isConsecutive && count($months) > 1) {
            $start = $this->monthNames[$months[0]] ?? '';
            $end = $this->monthNames[end($months)] ?? '';

            return "{$start} to {$end}";
        }

        $names = array_map(fn ($m) => $this->monthNames[$m] ?? '', $months);

        return implode(', ', $names);
    }
}
