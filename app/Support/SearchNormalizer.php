<?php

namespace App\Support;

/**
 * Normalizes a search query for catalog compatibility lookups.
 *
 * Rules applied (in order):
 *   1. UTF-8 lowercase
 *   2. Replace dots, dashes, underscores, slashes, backslashes with a space
 *   3. Collapse multiple whitespace characters into a single space
 *   4. Trim leading / trailing whitespace
 *
 * The same normalization must be applied at import time to the pre-computed
 * columns `device_models.model_normalized` and `part_numbers.value_normalized`.
 *
 * Usage (import / seeder):
 *   DeviceModel::create([
 *       'model_name'       => $row['exModel'],
 *       'model_normalized' => SearchNormalizer::normalize($row['exModel']),
 *       ...
 *   ]);
 *
 * Usage (search):
 *   $norm = SearchNormalizer::normalize($request->input('q'));
 *   DeviceModel::where('model_normalized', 'like', '%' . $norm . '%')->get();
 */
class SearchNormalizer
{
    public static function normalize(string $input): string
    {
        // 1. Lowercase (multibyte-safe)
        $result = mb_strtolower($input, 'UTF-8');

        // 2. Replace separator characters with space
        $result = str_replace(['.', '-', '_', '/', '\\', ','], ' ', $result);

        // 3. Collapse runs of whitespace
        $result = (string) preg_replace('/\s+/', ' ', $result);

        return trim($result);
    }
}
