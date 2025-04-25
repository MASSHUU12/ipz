<?php

namespace App\Jobs;

use App\Models\AirPollutionHistoricalData;
use App\Models\AirPollutionLeaderboard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateNewLeaderboard implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $latestDate = AirPollutionHistoricalData::query()
            ->selectRaw('MAX(DATE(created_at)) as date')
            ->value('date');

        if (! $latestDate) {
            return;
        }

        $records = AirPollutionHistoricalData::whereDate('created_at', $latestDate)
            ->get();

        if ($records->isEmpty()) {
            return;
        }

        // Remove any old leaderboard entries for that same date
        AirPollutionLeaderboard::whereDate('timestamp', $latestDate)->delete();

        $rows = $records->map(function (AirPollutionHistoricalData $r) {
            return [
                'station_name'      => $r->station_name,
                'city'              => $r->station_name ?? null,
                'air_quality_index' => $this->computeAirQualityIndex($r),
                'pm10'              => $r->pm10,
                'pm25'              => $r->pm25,
                'no2'               => $r->no2,
                'so2'               => $r->so2,
                'o3'                => $r->o3,
                'co'                => $r->co,
                'timestamp'         => $r->timestamp,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        })->toArray();

        AirPollutionLeaderboard::insert($rows);
    }

    /**
     * Compute a simple AQI as the average of all non‐null pollutant values.
     * TODO: replace this with a better formula.
     */
    private function computeAirQualityIndex(AirPollutionHistoricalData $r): ?float
    {
        $pollutants = ['pm10', 'pm25', 'no2', 'so2', 'o3', 'co'];
        $vals = [];

        foreach ($pollutants as $p) {
            if (! is_null($r->{$p})) {
                $vals[] = $r->{$p};
            }
        }

        if (count($vals) === 0) {
            return null;
        }

        return round(array_sum($vals) / count($vals), 2);
    }
}
