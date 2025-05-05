<?php

namespace App\Http\Controllers;

use App\Models\SynopticHistoricalData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ImgwApiClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImgwController extends Controller
{
    protected const CACHE_TTL          = 30;  // minutes
    protected const PRODUCTS_CACHE_TTL = 120; // minutes

    protected $client;

    public function __construct(ImgwApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Endpoint to fetch synoptic data.
     *
     * Example queries:
     *   /api/synop?id=12500
     *   /api/synop?station=jeleniagora
     *   /api/synop?format=html
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function synop(Request $request): JsonResponse
    {
        $id      = $request->query('id');
        $station = $request->query('station');
        $format  = $request->query('format', 'json');

        // Try to load the latest from your historical table
        $histQuery = SynopticHistoricalData::query();

        if ($id) {
            $histQuery->where('station_id', $id);
        } elseif ($station) {
            $histQuery->where('station_name', $station);
        }

        $latest = $histQuery
            ->orderByDesc('measurement_date')
            ->orderByDesc('measurement_hour')
            ->first();

        if ($latest) {
            return response()->json([
                'station_id'        => $latest->station_id,
                'station_name'      => $latest->station_name,
                'measurement_date'  => $latest->measurement_date->toDateString(),
                'measurement_hour'  => $latest->measurement_hour,
                'temperature'       => $latest->temperature,
                'wind_speed'        => $latest->wind_speed,
                'wind_direction'    => $latest->wind_direction,
                'relative_humidity' => $latest->relative_humidity,
                'rainfall_total'    => $latest->rainfall_total,
                'pressure'          => $latest->pressure,
            ]);
        }

        // If no historical data, fall back to the API + cache
        $cacheKey = 'synop_' . md5("id:{$id}_station:{$station}_format:{$format}");

        $data = Cache::remember(
            $cacheKey,
            now()->addMinutes(self::CACHE_TTL),
            function () use ($id, $station, $format) {
                return $this->client->getSynopData($id, $station, $format);
            }
        );

        if (!$data) {
            return response()->json(['error' => 'Unable to retrieve synoptic data'], 500);
        }

        try {
            SynopticHistoricalData::create([
                'station_id'        => $data['station_id'],
                'station_name'      => $data['station_name'],
                'measurement_date'  => $data['measurement_date'],
                'measurement_hour'  => $data['measurement_hour'],
                'temperature'       => $data['temperature']       ?? null,
                'wind_speed'        => $data['wind_speed']        ?? null,
                'wind_direction'    => $data['wind_direction']    ?? null,
                'relative_humidity' => $data['relative_humidity'] ?? null,
                'rainfall_total'    => $data['rainfall_total']    ?? null,
                'pressure'          => $data['pressure']          ?? null,
            ]);
        } catch (\Exception $e) {
            Log::warning('Could not persist synop data: ' . $e->getMessage());
        }

        return response()->json($data);
    }

    public function hydro(Request $request): JsonResponse
    {
        $variant = $request->query('hydro_variant', 1);
        $cacheKey = 'hydro_variant_' . $variant;

        $data = Cache::remember(
            $cacheKey,
            now()->addMinutes(self::CACHE_TTL),
            function () use ($variant) {
                return $this->client->getHydroData($variant);
            }
        );

        if ($data) {
            return response()->json($data);
        }

        return response()->json(['error' => 'Unable to retrieve hydrological data'], 500);
    }

    public function meteo(): JsonResponse
    {
        $cacheKey = 'meteo_data';

        $data = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL), function () {
            return $this->client->getMeteoData();
        });

        if ($data) {
            return response()->json($data);
        }

        return response()->json(['error' => 'Unable to retrieve meteorological data'], 500);
    }

    public function warningsMeteo(): JsonResponse
    {
        $cacheKey = 'warnings_meteo';

        $data = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL), function () {
            return $this->client->getWarningsMeteo();
        });

        if ($data) {
            return response()->json($data);
        }

        return response()->json(['error' => 'Unable to retrieve meteorological warnings'], 500);
    }

    public function warningsHydro(): JsonResponse
    {
        $cacheKey = 'warnings_hydro';

        $data = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL), function () {
            return $this->client->getWarningsHydro();
        });

        if ($data) {
            return response()->json($data);
        }

        return response()->json(['error' => 'Unable to retrieve hydrological warnings'], 500);
    }

    public function products(): JsonResponse
    {
        $cacheKey = 'products_data';

        $data = Cache::remember($cacheKey, now()->addMinutes(self::PRODUCTS_CACHE_TTL), function () {
            return $this->client->getProducts();
        });

        if ($data) {
            return response()->json($data);
        }

        return response()->json(['error' => 'Unable to retrieve product data'], 500);
    }
}
