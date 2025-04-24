<?php

namespace App\Jobs;

use App\Models\AirPollutionHistoricalData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;

class DeleteOldAirPollutionData implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job to delete historical data older than 3 days.
     */
    public function handle(): void
    {
        $cutoffDate = Carbon::now()->subDays(3);
        AirPollutionHistoricalData::where('created_at', '<', $cutoffDate)->delete();
    }
}
