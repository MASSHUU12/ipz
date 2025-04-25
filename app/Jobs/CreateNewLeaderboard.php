<?php

namespace App\Jobs;

use App\Models\AirPollutionHistoricalData;
use App\Models\AirPollutionLeaderboard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateNewLeaderboard implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * EPA Breakpoints for converting concentration -> AQI sub-index
     *
     * Format per pollutant: [
     *   [ Cp_low, Cp_high, I_low, I_high ],
     *   …
     *   ]
     *
     * Units:
     *  - pm25: µg/m³ (24-hr average)
     *  - pm10: µg/m³ (24-hr average)
     *  - no2, so2: ppb (1-hr)
     *  - o3: ppm  (8-hr)
     *  - co: ppm  (8-hr)
     */
    protected array $breakpoints = [
        'pm25' => [
            [0.0,   12.0,   0,   50],
            [12.1,  35.4,  51,  100],
            [35.5,  55.4, 101,  150],
            [55.5, 150.4, 151,  200],
            [150.5,250.4, 201,  300],
            [250.5,350.4, 301,  400],
            [350.5,500.4, 401,  500],
        ],
        'pm10' => [
            [0,   54,   0,   50],
            [55, 154,  51,  100],
            [155,254, 101,  150],
            [255,354, 151,  200],
            [355,424, 201,  300],
            [425,504, 301,  400],
            [505,604, 401,  500],
        ],
        'no2' => [
            [0,    53,    0,   50],
            [54,  100,   51,  100],
            [101, 360,  101,  150],
            [361, 649,  151,  200],
            [650,1249,  201,  300],
            [1250,1649, 301,  400],
            [1650,2049, 401,  500],
        ],
        'so2' => [
            [0,    35,    0,   50],
            [36,   75,   51,  100],
            [76,  185,  101,  150],
            [186,304,  151,  200],
            [305,604,  201,  300],
            [605,804,  301,  400],
            [805,1004, 401,  500],
        ],
        'o3' => [
            [0.000, 0.054,   0,   50],
            [0.055, 0.070,  51,  100],
            [0.071, 0.085, 101,  150],
            [0.086, 0.105, 151,  200],
            [0.106, 0.200, 201,  300],
            // beyond 0.2 ppm typically capped or handled separately
        ],
        'co' => [
            [0.0,   4.4,    0,   50],
            [4.5,   9.4,   51,  100],
            [9.5,  12.4,  101,  150],
            [12.5, 15.4,  151,  200],
            [15.5, 30.4,  201,  300],
            [30.5, 40.4,  301,  400],
            [40.5, 50.4,  401,  500],
        ],
    ];

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
     * Linear‐interpolate Cp into its AQI sub-index.
     */
    private function subIndex(?float $Cp, array $bps): ?int
    {
        if (is_null($Cp)) {
            return null;
        }

        foreach ($bps as [$Cplo, $Cphi, $Ilo, $Ihi]) {
            if ($Cp >= $Cplo && $Cp <= $Cphi) {
                // (Ihi−Ilo)/(Cphi−Cplo) × (Cp−Cplo) + Ilo
                return (int) round((($Ihi - $Ilo) / ($Cphi - $Cplo)) * ($Cp - $Cplo) + $Ilo);
            }
        }

        // Out of defined range → null (or cap at 500)
        return null;
    }

    /**
     * Compute AQI = max of all non-null sub-indices.
     */
    private function computeAirQualityIndex(AirPollutionHistoricalData $r): int
    {
        $subs = [];

        foreach (['pm25','pm10','no2','so2','o3','co'] as $pollutant) {
            if (isset($this->breakpoints[$pollutant])) {
                $idx = $this->subIndex($r->{$pollutant}, $this->breakpoints[$pollutant]);
                if (! is_null($idx)) {
                    $subs[] = $idx;
                }
            }
        }

        // NEVER return null — fallback to 0 if there were no valid readings
        return empty($subs) ? 0 : max($subs);
    }
}
