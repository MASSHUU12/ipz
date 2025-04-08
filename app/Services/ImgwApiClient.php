<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Docs: https://danepubliczne.imgw.pl/apiinfo
*/
class ImgwApiClient
{
    protected $baseUrl = 'https://danepubliczne.imgw.pl/api/data';

    /**
     * Retrieve synoptic data.
     *
     * Optional parameters:
     * - $id: Station ID.
     * - $station: Station name (without diacritical characters).
     * - $format: Response format ('json', 'xml', 'csv', or 'html').
     *
     * @param  string|null  $id
     * @param  string|null  $station
     * @param  string       $format
     * @return array|null
     */
    public function getSynopData($id = null, $station = null, $format = 'json'): array | null
    {
        $url = "{$this->baseUrl}/synop";

        if ($id) {
            $url .= "/id/{$id}";
        }

        if ($station) {
            $url .= "/station/" . strtolower($station);
        }

        if ($format && $format !== 'json') {
            $url .= "/format/{$format}";
        }

        $response = Http::get($url);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getHydroData($variant = 1): mixed
    {
        $base = $variant === 2 ? 'hydro2' : 'hydro';
        $url = "{$this->baseUrl}/{$base}";
        $response = Http::get($url);

        return $response->successful() ? $response->json() : null;
    }

    public function getMeteoData(): mixed
    {
        $url = "{$this->baseUrl}/meteo";
        $response = Http::get($url);

        return $response->successful() ? $response->json() : null;
    }

    public function getWarningsMeteo(): mixed
    {
        $url = "{$this->baseUrl}/warningsmeteo";
        $response = Http::get($url);

        return $response->successful() ? $response->json() : null;
    }

    public function getWarningsHydro(): mixed
    {
        $url = "{$this->baseUrl}/warningshydro";
        $response = Http::get($url);

        return $response->successful() ? $response->json() : null;
    }

    public function getProducts(): mixed
    {
        $url = "{$this->baseUrl}/product";
        $response = Http::get($url);

        return $response->successful() ? $response->json() : null;
    }
}
