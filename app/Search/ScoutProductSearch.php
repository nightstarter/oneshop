<?php

namespace App\Search;

use App\Contracts\ProductSearchInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Phase-2 search: delegates to Laravel Scout (Typesense, Meilisearch, Algolia…).
 *
 * To activate:
 *   1. In AppServiceProvider::register() change the binding:
 *        $this->app->bind(ProductSearchInterface::class, ScoutProductSearch::class);
 *   2. Set SCOUT_DRIVER=typesense (or desired driver) in .env.
 *   3. Run: php artisan scout:import "App\Models\Product"
 *
 * Product model already carries the Searchable trait and typesenseCollectionSchema().
 */
class ScoutProductSearch implements ProductSearchInterface
{
    public function search(string $query, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        /** @var \Laravel\Scout\Builder $builder */
        $builder = Product::search(trim($query))
            ->where('is_active', true);

        return $builder->paginate($perPage, 'page', $page);
    }
}
