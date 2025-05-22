<?php

namespace App\Http\Requests;

use App\Helpers\JaroWinklerHelper;
use App\Services\GiosApi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class GetAirQualityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'lat'  => 'required_without:addr|numeric|between:49.0,55.0', // Poland latitude range
            'lon'  => 'required_without:addr|numeric|between:14.0,24.0', // Poland longitude range
            'addr' => 'required_without_all:lat,lon|string',
        ];
    }

    /**
     * Return coordinates: either rounded lat/lon or resolved from addr.
     * @return array{0: float, 1: float}
     * @throws \RuntimeException if no matching location found.
     */
    public function coordinates(): array
    {
        // If lat and lon provided, use them (rounded for cache consistency)
        if ($this->filled(['lat', 'lon'])) {
            return [
                round($this->input('lat'), 3),
                round($this->input('lon'), 3),
            ];
        }

        // Otherwise, resolve from address fuzzy match
        $query = mb_strtolower($this->input('addr'));

        // Perform case-insensitive LIKE search, take distinct stations
        $candidates = DB::table('air_pollution_historical_data')
            ->select('station_name', 'latitude', 'longitude')
            ->whereRaw('LOWER(station_name) LIKE ?', ["%{$query}%"])
            ->distinct()
            ->limit(50)
            ->get()
            ->map(function ($row) {
                return [
                    'label'     => $row->station_name,
                    'latitude'  => (float) $row->latitude,
                    'longitude' => (float) $row->longitude,
                ];
            })
            ->all();

        // If no DB match, fallback to GIOS API
        if (empty($candidates)) {
            /** @var GiosApi $gios */
            $gios      = app(GiosApi::class);
            $stations  = $gios->getAllStations() ?: [];

            $matches = array_filter($stations, fn($s) => !empty($s['stationName']));

            // Compute Jaro-Winkler similarity
            $scores = array_map(
                fn($s) => JaroWinklerHelper::jaroWinkler($query, mb_strtolower($s['stationName'])),
                $matches
            );

            if (!empty($scores)) {
                // Pick station with highest similarity
                $bestIdx = array_keys($scores, max($scores))[0];
                $best    = array_values($matches)[$bestIdx];

                return [
                    (float) $best['gegrLat'],
                    (float) $best['gegrLon'],
                ];
            }

            throw new \RuntimeException("No location similar to “{$this->input('addr')}”");
        }

        // Compute Jaro-Winkler similarity for DB candidates
        $scores = array_map(
            fn($item) => JaroWinklerHelper::jaroWinkler($query, mb_strtolower($item['label'])),
            $candidates
        );

        $bestIdx = array_keys($scores, max($scores))[0];
        $best    = $candidates[$bestIdx];

        return [$best['latitude'], $best['longitude']];
    }
}
