<?php

namespace App\Search;

use App\Contracts\ProductSearchInterface;
use App\Models\Product;
use App\Support\SearchNormalizer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Phase-1 search: pure MySQL, no external services required.
 *
 * Search priority (ORDER BY rank ASC):
 *   0 – SEO product linked to a device_model whose normalized name matches the query
 *   1 – Carrier product with exact SKU match
 *   2 – Carrier / SEO product whose name starts with the raw query
 *   3 – Everything else (substring name, device_model, part_number hits)
 *
 * The query searches across:
 *   – product name (raw)
 *   – product SKU (raw)
 *   – device_models.model_normalized (via carrier lookup: COALESCE(parent_product_id, id))
 *   – part_numbers.value_normalized   (via carrier lookup)
 *
 * To switch to Typesense in Phase 2, change the binding in AppServiceProvider:
 *   $this->app->bind(ProductSearchInterface::class, ScoutProductSearch::class);
 * and set SCOUT_DRIVER=typesense in .env.
 */
class MysqlProductSearch implements ProductSearchInterface
{
    public function search(string $query, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $raw  = trim($query);
        $norm = SearchNormalizer::normalize($raw);
        $like = '%' . $norm . '%';

        return Product::query()
            // Active check: prefer `active` column, fall back to `is_active`
            ->where(function ($q) {
                $q->where('active', true)
                  ->orWhere(function ($q2) {
                      $q2->whereNull('active')->where('is_active', true);
                  });
            })
            ->where(function ($base) use ($raw, $norm, $like) {
                // Classic name / SKU search (raw input)
                $base->where('sku', $raw)
                     ->orWhere('name', 'like', '%' . $raw . '%');

                // Device model match (via carrier: COALESCE(parent_product_id, id))
                $base->orWhereExists(function ($sub) use ($like) {
                    $sub->selectRaw('1')
                        ->from('catalog_product_device_models as cdm')
                        ->join('device_models as dm', 'dm.id', '=', 'cdm.device_model_id')
                        ->whereRaw('cdm.product_id = COALESCE(products.parent_product_id, products.id)')
                        ->where('dm.model_normalized', 'like', $like);
                });

                // Part number match (via carrier)
                $base->orWhereExists(function ($sub) use ($like) {
                    $sub->selectRaw('1')
                        ->from('catalog_product_part_numbers as cpn')
                        ->join('part_numbers as pn', 'pn.id', '=', 'cpn.part_number_id')
                        ->whereRaw('cpn.product_id = COALESCE(products.parent_product_id, products.id)')
                        ->where('pn.value_normalized', 'like', $like);
                });
            })
            ->with(['categories', 'productImages.mediaFile'])
            /*
             * Rank expression:
             *   0 – SEO product linked to a device_model exactly matching the search
             *   1 – Carrier / SEO exact SKU
             *   2 – Carrier / SEO name prefix
             *   3 – Everything else
             *
             * Within the same rank, carriers (parent_product_id IS NULL) sort before
             * SEO variants, then alphabetically by name.
             */
            ->orderByRaw(
                'CASE
                    WHEN linked_device_model_id IS NOT NULL
                     AND linked_device_model_id IN (
                         SELECT id FROM device_models
                          WHERE model_normalized = ?
                     ) THEN 0
                    WHEN sku = ?             THEN 1
                    WHEN name LIKE ?         THEN 2
                    ELSE                          3
                 END',
                [$norm, $raw, $raw . '%']
            )
            ->orderByRaw('CASE WHEN parent_product_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}

