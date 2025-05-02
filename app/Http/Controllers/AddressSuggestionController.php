<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressSuggestionController extends Controller
{
    /**
     * GET /api/addresses/suggest?q=ciąg
     * Zwraca top 10 miast (kolumna `city`) z tabeli air_pollution_leaderboard
     * posortowane po odległości Levenshteina od q.
     */
    public function suggest(Request $request)
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

        $scored = array_map(function(string $addr) use ($q) {
            return [
                'address'  => $addr,
                'distance' => levenshtein(mb_strtolower($q), mb_strtolower($addr)),
            ];
        }, $candidates);

        usort($scored, fn($a, $b) => $a['distance'] <=> $b['distance']);


        $suggestions = array_slice(
            array_column($scored, 'address'),
            0,
            10
        );

        return response()->json(['suggestions' => $suggestions]);
    }
}
