<?php

namespace App\Helpers;

class JaroWinklerHelper
{
    /**
     * Compute the Jaro-Winkler distance between two strings.
     *
     * @param string $s1 First string.
     * @param string $s2 Second string.
     * @param float $prefixScale Scaling factor for common prefix (0.0 to 0.25). Default 0.1.
     * @return float Jaro-Winkler similarity between 0 (no similarity) and 1 (exact match).
     */
    public static function jaroWinkler(string $s1, string $s2, float $prefixScale = 0.1): float
    {
        $len1 = mb_strlen($s1);
        $len2 = mb_strlen($s2);

        if ($len1 === 0 && $len2 === 0) {
            return 1.0;
        }
        if ($len1 === 0 || $len2 === 0) {
            return 0.0;
        }

        // Maximum distance to match characters
        $matchDistance = (int) floor(max($len1, $len2) / 2) - 1;

        $s1Matches = array_fill(0, $len1, false);
        $s2Matches = array_fill(0, $len2, false);

        // Count matches
        $matches = 0;
        for ($i = 0; $i < $len1; $i++) {
            $start = max(0, $i - $matchDistance);
            $end = min($i + $matchDistance, $len2 - 1);
            for ($j = $start; $j <= $end; $j++) {
                if (!$s2Matches[$j] && mb_substr($s1, $i, 1) === mb_substr($s2, $j, 1)) {
                    $s1Matches[$i] = true;
                    $s2Matches[$j] = true;
                    $matches++;
                    break;
                }
            }
        }

        if ($matches === 0) {
            return 0.0;
        }

        // Count transpositions
        $k = 0;
        $transpositions = 0;
        for ($i = 0; $i < $len1; $i++) {
            if ($s1Matches[$i]) {
                while (!$s2Matches[$k]) {
                    $k++;
                }
                if (mb_substr($s1, $i, 1) !== mb_substr($s2, $k, 1)) {
                    $transpositions++;
                }
                $k++;
            }
        }
        $transpositions /= 2;

        // Jaro similarity
        $jaro = (
            ($matches / $len1) +
            ($matches / $len2) +
            (($matches - $transpositions) / $matches)
        ) / 3;

        // Common prefix length up to $maxPrefix
        $prefixLength = 0;
        $maxPrefix = 4;
        $max = min($maxPrefix, min($len1, $len2));
        for ($i = 0; $i < $max && mb_substr($s1, $i, 1) === mb_substr($s2, $i, 1); $i++) {
            $prefixLength++;
        }

        // Jaro-Winkler adjustment
        return $jaro + ($prefixLength * $prefixScale * (1 - $jaro));
    }
}
