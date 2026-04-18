<?php

namespace App\Search;

use App\Contracts\ProductSearchInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Phase-1 search: pure MySQL, no external services required.
 *
 * Strategy:
 *   1. Exact SKU match  → ranked first (CASE expression in ORDER BY).
 *   2. Name prefix match (name LIKE 'query%') → ranked second.
 *   3. Name substring match (name LIKE '%query%') → ranked third.
 *
 * All three conditions are combined in a single query so only active products
 * are returned and pagination works correctly.
 *
 * To switch to Typesense in Phase 2, change the binding in AppServiceProvider:
 *   $this->app->bind(ProductSearchInterface::class, ScoutProductSearch::class);
 * and set SCOUT_DRIVER=typesense in .env.
 */
class MysqlProductSearch implements ProductSearchInterface
{
    public function search(string $query, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $safe = trim($query);

        return Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($safe) {
                $q->where('sku', $safe)                          // exact SKU
                  ->orWhere('name', 'like', $safe . '%')         // prefix name
                  ->orWhere('name', 'like', '%' . $safe . '%');  // substring name
            })
                        ->with(['categories', 'productImages.mediaFile'])
            // Exact SKU hits bubble to top; prefix name before substring.
            ->orderByRaw(
                'CASE
                    WHEN sku = ?           THEN 0
                    WHEN name LIKE ?       THEN 1
                    ELSE                       2
                 END',
                [$safe, $safe . '%']
            )
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
