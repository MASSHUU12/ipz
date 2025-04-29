<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetAirQualityRequest;
use App\Services\AirQualityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AirQualityController extends Controller
{
    public function __construct(protected AirQualityService $aqService) {}

    public function __invoke(GetAirQualityRequest $request): JsonResponse
    {
        try {
            [$lat, $lon] = $request->coordinates();
            $data = $this->aqService->getForCoordinates($lat, $lon);

            return response()->json(array_merge(['success' => true], $data));
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        } catch (\Throwable $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve air quality data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
