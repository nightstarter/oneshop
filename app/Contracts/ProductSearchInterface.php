<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Abstraction over product full-text search.
 *
 * Phase 1: implemented by MysqlProductSearch (SKU exact + name prefix via LIKE).
 * Phase 2: swap binding to ScoutProductSearch and enable Typesense in .env.
 */
interface ProductSearchInterface
{
    /**
     * Search active products by a free-text query.
     *
     * @param  string  $query   Raw search term from the user.
     * @param  int     $perPage Number of results per page.
     * @param  int     $page    1-based page number.
     * @return LengthAwarePaginator<\App\Models\Product>
     */
    public function search(string $query, int $perPage = 20, int $page = 1): LengthAwarePaginator;
}
