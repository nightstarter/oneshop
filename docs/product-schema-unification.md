# Product Schema Unification

## Final schema
- `products.active` is the only product activation flag.
- `products.price` is the only base net product price.
- `products.stock_item_id` is the only inventory source of truth.
- Product stock for UI/API is derived from `Product::availableQuantity()`.
- Legacy CSV header mapping is preserved only inside the legacy import layer.

## Old to new map

| Old name | Final name | Status |
|---|---|---|
| `is_active` | `active` | Removed from runtime code, kept only for migration/backfill |
| `base_price_net` | `price` | Removed from runtime code, kept only for migration/backfill |
| `stock_qty` | `stock_item_id` + `stock_items.quantity` | Removed from runtime code, backfilled into stock items |

## Audit findings
- `app/Models/Product.php` carried both old and new columns and contained fallback reads.
- Query paths in catalog/search/cart/image visibility mixed `is_active` and `active`.
- Pricing still read `base_price_net` in `PriceCalculator`.
- Frontend product detail templates still rendered `stock_qty`.
- Admin product create/update validated and persisted the old payload keys.
- Legacy import and seeders were still writing both old and new columns.

## Patch plan by file group
- Domain/query layer:
  `app/Models/Product.php`, `app/Services/PriceCalculator.php`, `app/Services/CartService.php`, `app/Search/MysqlProductSearch.php`, `app/Search/ScoutProductSearch.php`, `app/Http/Controllers/Frontend/ProductCatalogController.php`, `app/Http/Controllers/Frontend/HomeController.php`, `app/Http/Controllers/Frontend/ProductImageViewController.php`, `app/Http/Controllers/Admin/ProductController.php`
- Write path:
  `app/Services/ProductWriteService.php`, `app/Http/Requests/StoreProductRequest.php`, `app/Http/Requests/UpdateProductRequest.php`, `app/Http/Controllers/Admin/ProductController.php`, `app/Http/Controllers/AdminWeb/ProductController.php`
- UI and seed data:
  `resources/views/admin/products/*.blade.php`, `resources/themes/*/products/show.blade.php`, `app/Services/Import/LegacyProductImportService.php`, `database/seeders/SharedStockCatalogSeeder.php`, `database/seeders/ModelSeoLandingSeeder.php`
- Legacy import mapping:
  `app/Services/Import/LegacyProductRowMapper.php`, `app/Services/Import/LegacyProductImportService.php`, `app/Console/Commands/ImportLegacyProducts.php`
- Migration and docs:
  `app/Services/ProductSchemaBackfillService.php`, `database/migrations/2026_04_24_120000_backfill_products_to_final_schema.php`, this document
- Tests:
  `tests/Unit/ProductTest.php`, `tests/Feature/PriceCalculatorTest.php`, `tests/Feature/ProductCatalogFlowTest.php`, `tests/Feature/CheckoutInventoryFlowTest.php`, `tests/Feature/ProductSchemaBackfillTest.php`

## Zero-downtime migration strategy

### 1. Additive phase
- State:
  New columns already exist in `products`.
- Risk:
  Mixed reads can hide missing backfill.
- Rollback:
  Keep old code path until backfill is verified.
- Verification:
  Count products where `price IS NULL`, `active IS NULL`, `stock_item_id IS NULL`.

### 2. Backfill data
- Action:
  Run `2026_04_24_120000_backfill_products_to_final_schema`.
- Risk:
  Products without a linked stock card need a safe default.
- Rollback:
  Restore DB backup or redeploy previous app version; this migration is intentionally operationally rolled back, not destructively reversed in code.
- Verification:
  Confirm zero rows with missing `price`, `active`, or `stock_item_id` unless explicitly exempted.

### 3. Import-only compatibility layer
- Action:
  Legacy field-name compatibility is preserved only for CSV import headers via `LegacyProductRowMapper`.
- Risk:
  External API/admin callers using old request keys become a breaking change after cleanup.
- Rollback:
  Restore the previous app build or reintroduce request normalization temporarily.
- Verification:
  Legacy import still accepts headers like `ItemCode`, `Closed`, `Cena1`, `Dispo`.

### 4. Switch reads to final names
- Action:
  Runtime code now reads only `active`, `price`, and stock via `stock_item_id`.
- Risk:
  Any missed read will surface as incorrect availability/visibility.
- Rollback:
  Revert application code only; data remains compatible.
- Verification:
  Catalog, cart, checkout, and stock deduction tests pass.

### 5. Remove old columns and compatibility
- Action:
  Drop `products.is_active`, `products.base_price_net`, `products.stock_qty`; keep legacy mapping only in the import layer.
- Risk:
  External clients still sending old request keys to admin/API.
- Rollback:
  Re-add columns from backup/point-in-time restore and redeploy the pre-cleanup build.
- Verification:
  Grep for old names outside migration/docs/import-backfill returns empty, and import still succeeds with legacy CSV headers.

## Assumptions
- `products.price` represents the base net price, matching existing price-list net logic.
- When a product misses `stock_item_id` but has a carrier parent with one, the parent stock item is the safest backfill target.
- For standalone products missing `stock_item_id`, creating a dedicated `stock_items` record is safer than keeping runtime fallback to `stock_qty`.
- Shared stock items are intentional, so product edits do not rewrite stock item identity fields for already linked stock items.

## Breaking changes
- Runtime reads no longer consult `products.is_active`, `products.base_price_net`, or `products.stock_qty`.
- Canonical write payload is `price`, `active`, `stock_item_id`, `stock_quantity`.
- Legacy request keys are no longer accepted by admin/API product write endpoints after cleanup.
- Legacy CSV headers remain supported for `import:legacy-products`.

## Production runbook
1. Take a DB backup / snapshot.
2. Deploy the compatibility build containing this code and the backfill migration.
3. Run `php artisan migrate --force`.
4. Verify:
   `SELECT COUNT(*) FROM products WHERE price IS NULL OR active IS NULL OR stock_item_id IS NULL;`
5. Run targeted smoke tests:
   catalog listing, product detail, add to cart, checkout, stock deduction.
6. Verify legacy import with original headers still works through `php artisan import:legacy-products ...`.
7. If rollback is needed, restore DB backup and redeploy the pre-cleanup build.
