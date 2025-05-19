<?php

namespace App\Http\Controllers;

use App\Helpers\JaroWinklerHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressSuggestionController extends Controller
{
    /**
     * GET /api/addresses/suggest?q=ciąg
     * Returns top 10 cities (column `city`) from table air_pollution_leaderboard
     * sorted by Jaro-Winkler similarity to q (highest first).
     */
    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['suggestions' => []]);
        }

        $candidates = DB::table('air_pollution_leaderboard')
            ->select('city as address')
            ->whereRaw('LOWER(city) LIKE ?', ['%' . strtolower($q) . '%'])
            ->distinct()
            ->limit(50)
            ->pluck('address')
            ->all();

        $qLower = mb_strtolower($q);

        // Score each candidate by Jaro-Winkler similarity
        $scored = array_map(function (string $addr) use ($qLower) {
            $addrLower = mb_strtolower($addr);
            return [
                'address'    => $addr,
                'similarity' => JaroWinklerHelper::jaroWinkler($qLower, $addrLower),
            ];
        }, $candidates);

        usort($scored, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        $suggestions = array_slice(
            array_column($scored, 'address'),
            0,
            10
        );

        return response()->json(['suggestions' => $suggestions]);
    }
}
