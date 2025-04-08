<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ImgwApiClient;
use Illuminate\Support\Facades\Cache;

class ImgwController extends Controller
{
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

        $cacheKey = 'synop_' . md5("id:{$id}_station:{$station}_format:{$format}");

        $data = Cache::remember(
            $cacheKey,
            now()->addMinutes(10),
            function () use ($id, $station, $format) {
                return $this->client->getSynopData($id, $station, $format);
            }
        );

        if ($data) {
            return response()->json($data);
        }

        return response()->json(['error' => 'Unable to retrieve synoptic data'], 500);
    }

    public function hydro(Request $request): JsonResponse
    {
        $variant = $request->query('hydro_variant', 1);
        $cacheKey = 'hydro_variant_' . $variant;

        $data = Cache::remember(
            $cacheKey,
            now()->addMinutes(10),
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

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () {
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

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () {
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

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () {
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

        $data = Cache::remember($cacheKey, now()->addMinutes(60), function () {
            return $this->client->getProducts();
        });

        if ($data) {
            return response()->json($data);
        }

        return response()->json(['error' => 'Unable to retrieve product data'], 500);
    }
}
