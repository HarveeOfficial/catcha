<?php

namespace App\Console\Commands;

use App\Models\Boat;
use Illuminate\Console\Command;

class ExpireBoats extends Command
{
    protected $signature = 'boats:expire';

    protected $description = 'Automatically expire boats whose expiry_date has passed';

    public function handle(): int
    {
        $expiredCount = Boat::where('status', 'active')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$expiredCount} boat(s).");

        return self::SUCCESS;
    }
}
