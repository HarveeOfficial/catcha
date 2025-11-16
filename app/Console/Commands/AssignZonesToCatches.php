<?php

namespace App\Console\Commands;

use App\Models\FishCatch;
use App\Models\Zone;
use App\Services\ZoneService;
use Illuminate\Console\Command;

class AssignZonesToCatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-zones-to-catches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign zones to catches based on their geographic coordinates';

    /**
     * Execute the console command.
     */
    public function handle(ZoneService $zoneService): int
    {
        $this->info('Starting zone assignment for catches...');

        $catches = FishCatch::whereNull('zone_id')->get();
        $zones = Zone::where('is_active', true)->get();

        if ($catches->isEmpty()) {
            $this->info('No catches without zones found.');

            return self::SUCCESS;
        }

        if ($zones->isEmpty()) {
            $this->warn('No active zones found.');

            return self::FAILURE;
        }

        $this->info("Processing {$catches->count()} catches without zones...");

        $bar = $this->output->createProgressBar($catches->count());
        $assigned = 0;
        $notFound = 0;

        foreach ($catches as $catch) {
            if ($catch->latitude === null || $catch->longitude === null) {
                $notFound++;
                $bar->advance();

                continue;
            }

            $zone = $zoneService->findZoneForCoordinates($catch->latitude, $catch->longitude);

            if ($zone) {
                $catch->update(['zone_id' => $zone->id]);
                $assigned++;
            } else {
                $notFound++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('✓ Assignment complete!');
        $this->info("  - Zones assigned: {$assigned}");
        $this->info("  - Not in any zone: {$notFound}");

        return self::SUCCESS;
    }
}
