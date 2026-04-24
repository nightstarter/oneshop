# PROJECT_CONTEXT

## 1. Stack
- Backend: PHP 8.1+, Laravel 10.10
- DB: MySQL 8
- Auth/API: Laravel Sanctum
- Search: Laravel Scout + typesense/typesense-php nainstalovano, aktivni binding je MysqlProductSearch
- Frontend build: Vite 5, laravel-vite-plugin, axios
- Testy: PHPUnit 10

## 2. Architektura
- Aplikace je rozdelena na 3 HTTP vrstvy:
  - Frontend controllery: app/Http/Controllers/Frontend
  - Admin web (Blade): app/Http/Controllers/AdminWeb
  - Admin API (JSON): app/Http/Controllers/Admin + routes/api.php
- Domenní operace jsou ve sluzbach: app/Services
- Externi/variantni chovani je za kontrakty + resolvery:
  - ProductSearchInterface -> MysqlProductSearch (binding v AppServiceProvider)
  - ShippingProviderInterface + ShippingProviderResolver
  - PaymentProviderInterface + PaymentProviderResolver
- Theme rendering je pres ShopSettings + ThemeView + RendersThemeViews trait
- Cart je unified service:
  - guest cart v session
  - user cart v DB (carts, cart_items)
  - merge guest cart po loginu pres listener MergeGuestCartOnLogin

## 3. Hlavni entity a vztahy
- User 1:1 Customer
- Customer N:1 PriceList, 1:N Address, 1:N Order
- Category self-tree: parent_id (parent/children)
- Product N:M Category (category_product)
- Product N:M PriceList s pivot price_net (price_list_product)
- Product N:1 StockItem
- StockItem self N:M pres stock_item_components (set/kit komponenty)
- Product N:M CompatibilityModel (product_compatibilities)
- Product N:M MediaFile pres product_images + explicitni ProductImage model
- Cart N:1 User, Cart 1:N CartItem, CartItem N:1 Product
- Order N:1 Customer/User/ShippingMethod/PaymentMethod
- Order 1:N OrderItem, Order 1:N PaymentTransaction
- ShippingMethod N:M PaymentMethod (shipping_payment_method)

## 4. Business logika (napr. sklad, sety)
- Pricing:
  - PriceCalculator bere default z products.price
  - pokud ma customer price_list_id a existuje pivot cena, pouzije price_list_product.price_net
  - DPH podle config('shop.vat_rate')
- Checkout:
  - CheckoutService validuje vyber dopravy a platby
  - uklada snapshot dopravy/platby do orders (kody, nazvy, ceny, payloady, pickup data)
  - po vytvoreni order spousti PaymentService::initiate
- Order + sklad:
  - OrderService vytvori order + order_items + totals
  - InventoryService::deductForOrder dela transakcni odecet skladu
  - u kitu odecita jak hlavni stock item, tak komponenty podle ratio
- Dostupnost produktu:
  - Product::isActiveForSale() cte pouze products.active
  - Product::hasStock() a StockItem::availableQuantityForSale() resi realnou dostupnost
- Doprava/platby:
  - dostupnost metod jde pres provider implementace
  - ZasilkovnaBox provider vyzaduje pickup point
  - PaymentService callback/return mapuje status na paid/failed a meni status order na confirmed/cancelled
- Obrazky:
  - ProductImageService deduplikuje soubory podle sha256 checksum
  - soubory jsou na private_products disku, render pres ProductImageViewController + ProductImageRenderService
  - runtime resize + watermark, fallback placeholder endpoint

## 5. Konvence (kde co patri)
- Modely: relace, fillable/casts, cast domenovych helper metod
- Services: hlavni business logika (cart, checkout, order, inventory, shipping, payment, pricing, image workflow)
- Providers/Contracts: swappovatelne implementace (search, shipping providers, payment providers)
- FormRequest:
  - pouzite hlavne u admin CRUD a image/composition akci
  - authorize() je vsude true; autorizace je primarne pres middleware (auth, admin)
- Controller konvence:
  - Frontend: orchestruje request/response + vola services
  - AdminWeb: Blade CRUD, cast validace pres FormRequest, cast inline request->validate
  - Admin API: JSON resource endpointy + FormRequest

## 6. Co je hotove
- Lokalizace CZ/EN textu ve storefront + admin casti
- Frontend katalog + detail + related products + add-to-cart flow
- Unified cart (session + DB) a merge po loginu
- Checkout flow vcetne dopravy, plateb a payment transaction zaznamu
- Snapshot dopravy/platby v orders
- Product image management v adminu (upload, alt, sort, main, delete)
- Bezpecne dorucovani obrazku z private storage + watermark + placeholder
- Shared-stock model (stock_items) a vazba produktu na stock kartu
- Skladove kompozice kitu (stock_item_components) + editace v adminu
- Order-time odecet skladu vcetne komponent u kitu
- Compatibility modely (compatibility_models, product_compatibilities)
- Admin web CRUD pro produkty, kategorie, cenniky, zakazniky, dopravy, platby, objednavky, payment transactions, themes
- Admin API endpointy pro products/categories/price-lists/customers/orders

## 7. CMS modul (stránky)

### Popis
Jednoduchý interní CMS pro správu statických obsahových stránek e-shopu.

### Datový model
- Tabulka `pages`: `id`, `title`, `slug` (unique), `content` (longText/HTML), `meta_title`, `meta_description`, `is_published` (bool), `published_at` (timestamp), `created_at`, `updated_at`
- Model: `app/Models/Page.php` — scope `scopePublished()`, casty `is_published` → bool, `published_at` → datetime

### Soubory modulu
| Soubor | Role |
|--------|------|
| `database/migrations/2026_04_20_100000_create_pages_table.php` | Migrace |
| `app/Models/Page.php` | Model |
| `app/Http/Requests/StorePageRequest.php` | Validace vytvoření |
| `app/Http/Requests/UpdatePageRequest.php` | Validace editace (slug `unique()->ignore()`) |
| `app/Http/Controllers/AdminWeb/PageController.php` | Admin CRUD |
| `app/Http/Controllers/Frontend/PageController.php` | Frontend zobrazení |
| `resources/views/admin/pages/index.blade.php` | Admin seznam |
| `resources/views/admin/pages/form.blade.php` | Admin formulář + TinyMCE + auto-slug |
| `resources/themes/default/pages/show.blade.php` | Frontend view |

### Routy
- `GET /info/{slug}` → `Frontend\PageController@show` (veřejná, pouze publikované; 404 jinak)
- `GET|POST /admin/pages` → seznam + vytvoření
- `GET /admin/pages/create` → formulář
- `GET|PUT|DELETE /admin/pages/{page}` → editace, smazání
- Admin routy: middleware `['auth', 'admin']`

### WYSIWYG editor
- TinyMCE 8 nainstalován přes npm (`npm install tinymce`), **self-hosted bez CDN**
- Assety jsou v `public/js/tinymce/` (zkopírovány npm `postinstall` skriptem `scripts/copy-tinymce.cjs`)
- Načten přes `@push('scripts')` pouze na stránce formuláře: `<script src="/js/tinymce/tinymce.min.js">`
- Konfigurace: `license_key: 'gpl'` (open-source verze, bez API klíče, bez CDN notifikací)
- Po každém `npm install` se assety automaticky zkopírují; lze také ručně: `node scripts/copy-tinymce.cjs`
- Složka `public/js/tinymce/` je doporučeno přidat do `.gitignore` (generovaný artefakt)

### XSS přístup
- Obsah zadávají výhradně autentizovaní admini (middleware `auth` + `is_admin`)
- Ukládá se jako raw HTML, renderuje se pomocí `{!! $page->content !!}`
- Nikdy nevystavovat toto pole uživatelům bez `is_admin`

### Budoucí rozšíření (připravená místa)
- **Preview**: `scopePublished()` jednoduše odstranit pro admin preview route
- **Draft / plánované publikování**: `published_at` sloupec je připraven
- **Menu**: `Page::published()->get(['title','slug'])` kdekoli v layoutu
- **Více jazyků**: model obalit `spatie/laravel-translatable`
- **Řazení**: přidat `sort_order` sloupec do migrace + `orderBy()` do scopePublished

## 8. Co je rozpracovane
- Search Phase 2 (Scout/Typesense) je pripraveny, ale aktivni binding zustava na MysqlProductSearch
- Produktovy model/schema je po runtime strance sjednoceny:
  - domena a query vrstva pouziva `active`, `price`, `stock_item_id`
  - `base_price_net`, `stock_qty`, `is_active` uz nejsou pouzivane v runtime logice produktu
  - legacy request klice pro admin/API product write endpointy jsou breaking change a nejsou uz podporovane
  - legacy import puvodnich CSV hlavicek je zachovan jen v `App\Services\Import\LegacyProductRowMapper`
  - finalni cleanup release ma odstranit legacy sloupce z DB po overeni produkcniho runbooku
- Validace neni sjednocena:
  - cast admin/checkout endpointu pouziva FormRequest
  - cast endpointu pouziva inline request->validate
- Test coverage je rozsirená, ale porad ne uplna:
  - pribyly unit/feature testy pro produktovou domenu, katalog, kosik, checkout, odečet skladu a backfill
  - stale chybi sirsi pokryti admin CRUD toku, importu a Search Phase 2 integrace
