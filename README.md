# OneShop

Laravel 10 kostra e-shopu bez variant produktů, s podporou:

- many-to-many vazby produktu na kategorie
- jedné globální sazby DPH z `config/shop.php`
- B2B ceníků přes `price_lists` a `price_list_product`
- vyhledávání produktů přes MySQL (fáze 1) s připravenou cestou na Laravel Scout + Typesense
- ukládání cenového snapshotu do objednávky
- sdílených obrázků bez duplicit souborů

## Stack

- PHP 8.1+
- Laravel 10
- MySQL 8
- Laravel Scout (nainstalovaný, fáze 1 neaktivní — `SCOUT_DRIVER=null`)

## Historie změn

Detailní průběžná historie implementačních kroků je vedena v samostatném souboru:

- [steps.md](steps.md)

## Hlavní doména

### Katalog

- `products`: produkt bez variant, drží základní netto cenu
- `categories`: strom kategorií přes `parent_id`
- `category_product`: many-to-many pivot mezi produktem a kategorií

### B2B ceny

- `price_lists`: definice ceníků
- `price_list_product`: přepis netto ceny produktu pro konkrétní ceník
- `customers.price_list_id`: zákazník může být přiřazen k ceníku

### Objednávky

- `orders`: souhrn objednávky a snapshot adres
- `order_items`: snapshot položek včetně netto, DPH a brutto hodnot

### Média

- `media_files`: jeden fyzický soubor evidovaný jednou
- `product_images`: pivot mezi produktem a souborem

## Klíčové třídy

- `App\Models\Product`
- `App\Models\Category`
- `App\Models\PriceList`
- `App\Models\Customer`
- `App\Models\Order`
- `App\Services\PriceCalculator`
- `App\Services\OrderService`

## Cenotvorba

1. Výchozí cena se bere z `products.price`.
2. Pokud má zákazník přiřazený ceník a pro produkt existuje záznam v `price_list_product`, použije se jeho `price_net`.
3. DPH se dopočítává globálně přes `config('shop.vat_rate')`.
4. Do `orders` a `order_items` se ukládají finální vypočtené částky, aby se historická objednávka neměnila při pozdější změně katalogu.

## Vyhledávání

Vyhledávání je zapouzdřeno za rozhraním `App\Contracts\ProductSearchInterface`.
Controller ani ostatní kód neví, co je za ním — přechod na jiný engine nevyžaduje změny
v controllerech ani šablonách.

### Fáze 1 – MySQL (výchozí)

Implementace `App\Search\MysqlProductSearch` nepotřebuje žádnou externí službu:

| Priorita | Podmínka | Příklad dotazu `"shirt"` |
|----------|----------|-------------------------|
| 0 | `sku = $query` | přesná shoda SKU |
| 1 | `name LIKE 'query%'` | prefix – „Shirt Basic" |
| 2 | `name LIKE '%query%'` | substring – „Blue Shirt" |

Aktivní vazba je nastavena v `AppServiceProvider::register()`:

```php
$this->app->bind(ProductSearchInterface::class, MysqlProductSearch::class);
```

### Fáze 2 – přechod na Typesense

**1. Spustit Typesense** (lokálně nebo v cloudu)

Příklad lokálního spuštění bez Dockeru (stažený binární soubor):

```powershell
typesense-server --api-key=xyz --data-dir=C:\typesense-data
```

**2. Aktualizovat `.env`**

```dotenv
SCOUT_DRIVER=typesense
TYPESENSE_API_KEY=xyz
TYPESENSE_HOST=127.0.0.1
TYPESENSE_PORT=8108
TYPESENSE_PROTOCOL=http
```

**3. Vyměnit binding v `AppServiceProvider::register()`**

```php
use App\Search\ScoutProductSearch;

$this->app->bind(ProductSearchInterface::class, ScoutProductSearch::class);
```

**4. Importovat katalog do indexu**

```powershell
php artisan scout:import "App\Models\Product"
```

Hotovo – žádná jiná změna v kódu není potřeba.
Model `Product` má trait `Searchable` a `typesenseCollectionSchema()` připravené od fáze 1.

### Přehled tříd

| Třída | Účel |
|-------|------|
| `App\Contracts\ProductSearchInterface` | Kontrakt – jediné místo, které musí controller znát |
| `App\Search\MysqlProductSearch` | Fáze 1 – čistý Eloquent, bez závislostí |
| `App\Search\ScoutProductSearch` | Fáze 2 – deleguje na `Product::search()` (Scout) |

## Lokální spuštění (fáze 1 – bez Dockeru)

```powershell
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

MySQL musí běžet lokálně (WAMP, XAMPP, Laragon apod.).
Přihlašovací údaje jsou v `.env` a `.env.example`.


## Poznámky k administraci

- `users.is_admin` slouží jako jednoduchý základ pro oddělení administrace.
- Pro admin rozhraní je vhodné navázat klasickými resource controllery a Form Request validací.
- Cenové výpočty by měly zůstat soustředěné do služeb, ne v controllerech.

## Modulární doprava a platby

Projekt obsahuje rozšiřitelný modul dopravy a plateb bez externích balíčků.

### Datový model

- `shipping_methods`
- `payment_methods`
- `shipping_payment_method` (kompatibilita doprava <-> platba)
- `payment_transactions`
- rozšíření `orders` o snapshot dopravy/platby a pickup metadata:
	- `shipping_method_id`, `shipping_code`, `shipping_name`, `shipping_price_net`, `shipping_price_gross`
	- `payment_method_id`, `payment_code`, `payment_name`, `payment_price_net`, `payment_price_gross`
	- `pickup_point_id`, `pickup_point_name`, `pickup_point_address`
	- `shipping_payload_json`, `payment_payload_json`

Objednávka ukládá snapshot, takže historická data zůstávají stabilní i po změně metod v administraci.

### Architektura providerů

Logika není v controllerech. Výběr implementace probíhá přes resolver.

- Contracts:
	- `App\Contracts\ShippingProviderInterface`
	- `App\Contracts\PaymentProviderInterface`
- Resolvers:
	- `App\Services\ShippingProviderResolver`
	- `App\Services\PaymentProviderResolver`
- Shipping providers:
	- `PersonalPickupShippingProvider`
	- `ZasilkovnaBoxShippingProvider`
- Payment providers:
	- `BankTransferPaymentProvider`
	- `CashOnDeliveryPaymentProvider`
	- `ComgatePaymentProvider`

### Servisní vrstva

- `ShippingService`:
	- vrací dostupné aktivní dopravy
	- validuje volbu dopravy
	- validuje provider-specific payload (např. pickup point)
- `PaymentService`:
	- vrací dostupné aktivní platby pro vybranou dopravu
	- validuje kompatibilitu doprava/platba
	- vytváří `payment_transactions`
	- zahajuje redirect/offline flow přes provider
	- zpracovává callback a return z gateway
- `CheckoutService`:
	- orchestrace checkoutu
	- vytvoření objednávky se snapshotem dopravy/platby
	- po vytvoření objednávky zahájení platby

### Checkout flow

1. Načtou se dostupné dopravy (`ShippingService::availableMethods`).
2. Podle vybrané dopravy se načtou dostupné platby (`PaymentService::availableMethodsForShipping`).
3. Provider dopravy validuje payload (u Zasilkovna Box je povinný pickup point).
4. Vytvoří se objednávka se snapshotem dopravy a platby.
5. Vytvoří se samostatná `payment_transaction`.
6. U redirect plateb (Comgate) zákazník dostane redirect URL na gateway.

### Routy

- Checkout:
	- `GET /checkout`
	- `GET /checkout/payment-methods` (dynamické platby podle dopravy)
	- `POST /checkout`
	- `GET /checkout/success/{order}`
- Gateway:
	- `POST /payments/{provider}/callback`
	- `GET /payments/{provider}/return`
- Admin web:
	- `resource admin/shipping-methods`
	- `resource admin/payment-methods`

### Administrace

V administraci lze:

- spravovat CRUD doprav
- spravovat CRUD plateb
- přiřadit kompatibilní platby ke konkrétní dopravě

To zajišťuje validaci kompatibility v checkoutu bez hardcodu v controllerech.

### Default metody a seeding

Seeder `ShippingAndPaymentMethodsSeeder` vytvori vychozi konfiguraci:

- vychozi doprava: `GLS na adresu` (`provider_code = gls_address`, `sort_order = 10`)
- dalsi dopravy: `Zasilkovna Box`, `Osobni odber`
- vychozi platba: `Platba prevodem na ucet` (`provider_code = bank_transfer`, `sort_order = 10`)
- dalsi platby: `Dobirka`, `Comgate redirect`
- kompatibility doprava <-> platba pres pivot `shipping_payment_method`

Spusteni:

```powershell
php artisan db:seed
```

### QR platba prevodem

Po vytvoreni objednavky se u bankovniho prevodu vytvori platebni instrukce a QR kod.
QR je dostupny na success strance objednavky.

Konfigurace v `.env`:

```dotenv
BANK_TRANSFER_ACCOUNT_NUMBER=123456789
BANK_TRANSFER_BANK_CODE=0100
BANK_TRANSFER_IBAN=
BANK_TRANSFER_BIC=
BANK_TRANSFER_MESSAGE="Platba za objednavku"
```

### Comgate (real API)

`ComgatePaymentProvider` vola realne endpointy:

- create payment: `COMGATE_CREATE_URL`
- status payment: `COMGATE_STATUS_URL`

Konfigurace v `.env`:

```dotenv
COMGATE_MERCHANT=
COMGATE_SECRET=
COMGATE_TEST=true
COMGATE_CREATE_URL=https://payments.comgate.cz/v1.0/create
COMGATE_STATUS_URL=https://payments.comgate.cz/v1.0/status
COMGATE_TIMEOUT_SECONDS=15
```

### Admin transakce

V administraci je nova sekce `Transakce`:

- seznam: `admin/payment-transactions`
- detail: `admin/payment-transactions/{id}`

## Seedery a import legacy katalogu

Projekt obsahuje seedery referencnich dat a idempotentni import legacy CSV souboru
pro produkty, kompatibilni modely, typova oznaceni a SEO karty.

### 1) Pripravit databazi

```powershell
php artisan migrate
php artisan db:seed
```

`db:seed` spousti mimo jine i tyto seedery:

- `ProductTypesSeeder`
- `ProductAttributesSeeder`
- `WarehousesSeeder`
- `PriceListsSeeder`

### 2) Pripravit importni soubory

Podporovane vstupy (CSV s hlavickou):

- `products.csv` - legacy produktovy export
- `ex_models.csv` - vazby modelu (`exID`, `exArtId`, `exModel`)
- `ex_types.csv` - vazby typovych oznaceni (`exID`, `exArtId`, `exTyp`)
- `seo_products.csv` - volitelne SEO produktove karty

Expected CSV headers (checklist):

| Soubor | Povinne sloupce | Doporucene volitelne sloupce |
|---|---|---|
| `products.csv` | `ItemCode` | `ItemName`, `Typ`, `InfoText`, `Cena1`, `Cena2`, `Cena5`, `Cena6`, `Cena7`, `Cena8`, `Dispo`, `Berlin_Stav`, `Berlin_Datum`, `Vyprodej`, `Kapacita`, `Volt`, `Plug`, `Barva`, `Hmotnost`, `KatalogoveCislo`, `Vyrobce`, `ModelGroup`, `ModelTyp`, `Ean`, `Original`, `Nadotaz`, `GrpId`, `sphinx_id`, `Obrazek`, `Obrazek2` |
| `ex_models.csv` | `exArtId`, `exModel` | `exID` |
| `ex_types.csv` | `exArtId`, `exTyp` | `exID` |
| `seo_products.csv` | `SeoSku`, `ParentItemCode` | `SeoName`, `SeoSlug`, `SeoDescription`, `LinkedModel` |

Dulezite pro `products.csv`:

- sloupec `Typ` uz neurcuje `product_type_id`; importuje se jako parametr chemie baterie (`chemistry`)
- urceni `product_type_id` probiha podminkami:
	- pokud `ItemName` obsahuje `nabijecka` (resp. `nabíječka`) nebo `charger` -> `charger`
	- jinak pokud je `Typ` neprazdny -> `battery`
	- jinak -> `product_type_id = null`
Import obrazku produktu:

- sloupec `Obrazek` – nazev souboru hlavniho obrazku produktu (napr. `bat-001.jpg`)
- sloupec `Obrazek2` – dalsi obrazky oddelene carkou (napr. `bat-001-b.jpg,bat-001-c.jpg`)
- soubory musi fyzicky existovat na disku `private_products` (viz `PRODUCT_IMAGES_PATH`)
- import hleda soubor primo v koreni disku i v podadresarich prvni urovne (napr. `2024/01/bat-001.jpg`)
- zarucena deduplikace pres sha256 checksum – stejny soubor se do `media_files` ulozi jen jednou
- pokud soubor na disku nenalezen, zapise se chyba do reportu a import pokracuje dal
- pocet napojenych obrazku je vykazan v reportu (`Napojene obrazky`)
### 3) Dry-run import (bez zapisu)

```powershell
php artisan import:legacy-products \
	--products=storage/import/products.csv \
	--models=storage/import/ex_models.csv \
	--types=storage/import/ex_types.csv \
	--dry-run
```

### 4) Realny import

```powershell
php artisan import:legacy-products \
	--products=storage/import/products.csv \
	--models=storage/import/ex_models.csv \
	--types=storage/import/ex_types.csv \
	--seo=storage/import/seo_products.csv \
	--errors-csv=storage/import/errors.csv
```

Volitelne parametry:

- `--separator=;` pro strednikovy CSV export
- `--encoding=Windows-1250` pro starsi exporty v CP1250

### 5) Co command importuje

- nosne produkty (`sku`, typ produktu, aktivita, fallback sloupce)
- atributy do `product_attribute_values`
- ceny do `product_prices` (mapa `Cena1..Cena8` na ceníky)
- skladove stavy do `product_stocks` (`MAIN`, `BERLIN`, `SALE`)
- kompatibilni modely (`device_models`) a typova oznaceni (`part_numbers`)
- SEO produkty navazane na nosny produkt

Import je idempotentni (`updateOrCreate`) a po behu vypise souhrn:

- vytvorene/aktualizovane produkty
- vytvorene/aktualizovane SEO produkty
- pocet vazeb modelu a typovych oznaceni
- pocet cenovych a skladovych zaznamu
- chyby (vcetne detailu) + volitelny export do CSV

### Troubleshooting

Nejbeznejsi problemy pri importu a jejich reseni:

1. Chyba: soubor nenalezen
- over cestu predanou v `--products`, `--models`, `--types`, `--seo`
- cesty jsou vyhodnocene relativne k rootu projektu

2. Chyba: prazdna nebo neplatna hlavicka CSV
- zkontroluj, ze soubor ma hlavickovy radek
- over separator (`--separator=,` nebo `--separator=;`)

3. Rozbite znaky (diakritika)
- nastav spravne kodovani (`--encoding=UTF-8` nebo `--encoding=Windows-1250`)

4. Vazby exModel/exTyp hlasi nenalezeny nosny produkt
- `exArtId` musi odpovidat `ItemCode` z nosneho produktu
- nejdriv naimportuj `products.csv`, potom `ex_models.csv` a `ex_types.csv`

5. Chybi produktovy typ po importu
- over obsah sloupce `Typ` a mapovani vyse
- pokud je potreba nova hodnota, dopln mapu v `resolveTypeCode()`

6. Ceny nebo sklady se neimportuji
- ceny se berou ze sloupcu `Cena1`, `Cena2`, `Cena5`, `Cena6`, `Cena7`, `Cena8`
- sklad se bere ze sloupcu `Dispo`, `Berlin_Stav`, `Vyprodej`
- prazdne nebo nulove hodnoty se mohou preskocit dle pravidel importu

7. Potrebuji zjistit detail chyb
- pouzij `--errors-csv=storage/import/errors.csv`
- command vraci tabulku chyb i do konzole

8. Migrace pada na `SQLSTATE[42S01]` (table already exists)
- typicky jde o stav, kdy cast tabulek uz byla vytvorena rucne nebo starsim pokusem o migraci
- po aktualizaci kodu spust znovu `php artisan migrate` (kompatibilitni migrace je osetrena pro partially-existing schema)
- over stav migraci pres `php artisan migrate:status`
- pokud problem pretrva, zkontroluj konzistenci DB (existence tabulek vs. zaznam v tabulce `migrations`)

## Překlady (i18n)

Projekt je plně lokalizován. Všechny uživatelské texty procházejí přes `__()` nebo `trans()` — žádné hardcodované řetězce ve views ani controllerech.

### Podporované jazyky

| Kód | Jazyk | Složka |
|-----|-------|--------|
| `cs` | Čeština (výchozí) | `lang/cs/` |
| `en` | Angličtina | `lang/en/` |

Výchozí jazyk se nastavuje v `.env`:

```dotenv
APP_LOCALE=cs
APP_FALLBACK_LOCALE=en
```

### Překladové soubory

| Soubor | Obsah |
|--------|-------|
| `lang/{locale}/shop.php` | Obecné texty e-shopu a admin rozhraní (katalog, objednávky, doprava, platby, zákazníci) |
| `lang/{locale}/forms.php` | Popisky formulářových polí |
| `lang/{locale}/buttons.php` | Texty tlačítek |
| `lang/{locale}/messages.php` | Flash zprávy (potvrzení akcí, chybové hlášky, stav košíku, checkoutu) |
| `lang/{locale}/validation.php` | Přeložená validační pravidla a mapování názvů polí (`attributes`) |
| `lang/{locale}/auth.php` | Autentizační hlášky (nesprávné heslo, throttle) |

### Flash zprávy (messages.php)

Pokrytí: košík, checkout, registrace, přihlášení, admin CRUD pro všechny entity (kategorie, zákazníci, ceníky, produkty, dopravy, platby, objednávky), chyby při výběru dopravy/platby.

### Validační překlady (validation.php)

Soubor obsahuje překlady validačních pravidel a klíč `attributes` s mapováním technických názvů polí na čitelné české/anglické popisky. Pokrytí:

- Pole objednávky a checkoutu: `product_id`, `quantity`, `shipping_method_id`, `payment_method_id`, `pickup_point_id`
- Fakturační a doručovací adresa: `billing_*`, `shipping_*`
- Produktová pole: `sku`, `price`, `stock_quantity`, `category_ids`
- Admin pole: `code`, `currency`, `valid_from`, `valid_to`, `price_net`, `price_gross`, `payment_method_ids`, `status`, `note` a další

### Přidání nového jazyka

1. Zkopírovat složku `lang/cs/` jako `lang/{novy_kod}/`.
2. Přeložit hodnoty ve všech souborech.
3. Nastavit `APP_LOCALE={novy_kod}` v `.env` nebo přidat přepínač jazyka do UI.

## Systém obrázků produktů

Projekt používá neveřejné ukládání obrázků a render přes Laravel route/controller.
Soubor obrázku není přístupný přímo z `public` URL.

### Datový model

- `media_files`: metadata fyzického souboru (`disk`, `path`, `mime_type`, `checksum`, rozměry)
- `product_images`: vazba produktu na obrázek + metadata galerie (`sort_order`, `alt`, `is_primary`)

Tabulka `product_images` je rozšířena pro pohodlnou správu v administraci:

- `id`
- `timestamps`
- unikátní kombinace `product_id + media_file_id`

### Uložení souborů

- Disk: `private_products`
- Cesta: `storage/app/private/products`
- Konfigurace: `config/filesystems.php`
- Při uploadu se zachovává původní název souboru (filename) v rámci storage i v DB (`media_files.original_name`)
- Pokud stejný název již existuje, přidá se suffix `-1`, `-2`, ... bez přepsání existujícího souboru

Originální soubory zůstávají vždy bez watermarku.

### Frontend render obrázků

Veřejné URL obrázků běží přes route:

- `GET /images/products/{mediaFile}`

Controller:

- `App\Http\Controllers\Frontend\ProductImageViewController`

Servisní vrstva:

- `App\Services\ProductImageRenderService`

Render pipeline:

1. Načtení originálu ze soukromého disku.
2. Volitelné přepočítání varianty (`main`, `thumb`).
3. Aplikace watermarku až při výstupu.
4. Vrácení bezpečné image response (`Content-Type`, `ETag`, `Last-Modified`, `X-Content-Type-Options`).

Přístupová pravidla:

- obrázky aktivních produktů jsou veřejné
- obrázky neaktivních produktů pouze pro admin uživatele

### Placeholder obrázek (produkt bez fotky)

Pro produkty bez přiřazeného obrázku se používá route placeholderu:

- `GET /images/products/placeholder`

Placeholder je generován jako SVG v controlleru a používá se v:

- produktových kartách (`default`, `studio`, `mono` theme)
- detailu produktu (`default`, `studio` theme)

Text placeholderu je lokalizovaný přes:

- `shop.image_placeholder_text`
- `shop.image_placeholder_alt`

### Administrace obrázků

Správa obrázků produktu je dostupná na formuláři editace produktu.

Podporované akce:

- upload jednoho nebo více obrázků
- seznam obrázků produktu
- označení hlavního obrázku
- změna pořadí (`sort_order`)
- editace `alt` textu
- smazání obrázku

Admin routes:

- `POST admin/products/{product}/images`
- `PATCH admin/products/{product}/images/{productImage}`
- `POST admin/products/{product}/images/{productImage}/main`
- `DELETE admin/products/{product}/images/{productImage}`

Controller:

- `App\Http\Controllers\AdminWeb\ProductImageController`

Business logika:

- `App\Services\ProductImageService`

### Watermark nastavení

Konfigurace watermark textu je v:

- `config/shop.php` (`image_watermark_text`)
- `.env` přes `SHOP_IMAGE_WATERMARK_TEXT`

Watermark se při výstupu vykresluje diagonálně a opakuje se přes celou plochu obrázku.

Příklad:

```dotenv
SHOP_IMAGE_WATERMARK_TEXT="OneShop"
```

### Migrace

Po nasazení změn spusťte:

```powershell
php artisan migrate
```

## Sdílený sklad pro více produktových karet

Následující model řeší situaci, kdy jedna fyzická skladová položka (např. baterie)
je prodávána přes více produktových karet (např. "Baterie pro Asus X555L", "Baterie pro Asus X555LA").
Sklad se vede pouze na úrovni `stock_items`.

### Architektonické vysvětlení

- `stock_items`: fyzické položky ve skladu (jediný zdroj pravdy pro množství)
- `products`: produktové karty v e-shopu (SEO texty, landing pages, různé názvy)
- `compatibility_models`: modely zařízení (např. notebook modely)
- `product_compatibilities`: M:N vazba mezi produktovou kartou a kompatibilními modely

Vazby:

- 1 `stock_item` -> N `products`
- 1 `product` -> N `compatibility_models` (přes pivot)

### Migrace

Nové migrace:

- `2026_04_18_100100_create_stock_items_table.php`
- `2026_04_18_100200_add_stock_item_and_visibility_to_products_table.php`
- `2026_04_18_100300_create_compatibility_models_table.php`
- `2026_04_18_100400_create_product_compatibilities_table.php`

### Modely

Nové modely:

- `App\Models\StockItem`
- `App\Models\CompatibilityModel`
- `App\Models\ProductCompatibility`

Upravený model:

- `App\Models\Product` (relace na `stockItem`, `compatibilityModels`, helpery pro dostupnost)

### Příklady použití

#### 1) Dostupnost produktu přes stock item

```php
$product = Product::query()->with('stockItem')->findOrFail($productId);

$availableQty = $product->availableQuantity();
$isInStock = $product->hasStock(2);
```

#### 2) Odečet skladu po vytvoření objednávky

Použijte servis `App\Services\InventoryService`:

```php
$inventoryService->deductForOrder($order);
```

Servis agreguje položky objednávky podle `stock_item_id`, zamkne záznamy `FOR UPDATE`
a provede atomický odečet z `stock_items.quantity`.

#### 3) Výpis kompatibilních modelů na detailu produktu

```php
$product = Product::query()
	->with('compatibilityModels')
	->findOrFail($productId);

$models = $product->compatibilityModels
	->where('active', true)
	->map(fn ($m) => $m->brand . ' ' . $m->model_name);
```

### Rozšiřitelnost pro další typy zboží

Typ fyzické položky je veden ve `stock_items.product_type` (např. `battery`, `charger`, `battery_kit`).
To umožňuje:

- sdílet společnou skladovou logiku napříč typy zboží
- zachovat jednotné napojení přes `products.stock_item_id`
- doplnit typově specifická pravidla bez změny základního skladu

Pro složené sety (např. baterie + nabíječka) lze v další fázi přidat tabulku typu
`stock_item_components` a počítat dostupnost setu jako minimum dostupností komponent.

### SEO landing pages bez duplikace skladu

Doporučený postup:

1. Pro každý notebook model použít samostatnou produktovou kartu (`products`) s vlastním SEO obsahem (`name`, `slug`, `description`).
2. Více těchto karet napojit na stejný `stock_item_id`.
3. Kompatibilní modely držet v `compatibility_models` + pivot `product_compatibilities`.
4. Pro landing stránky dle modelu použít route např. `/baterie/{model:slug}` a filtrovat produkty přes `compatibilityModels`.

Výsledek: mnoho SEO vstupních stránek, ale jeden společný sklad bez duplikace množství.
