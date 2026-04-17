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

1. Výchozí cena se bere z `products.base_price_net`.
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
- Produktová pole: `sku`, `base_price_net`, `stock_qty`, `category_ids`
- Admin pole: `code`, `currency`, `valid_from`, `valid_to`, `price_net`, `price_gross`, `payment_method_ids`, `status`, `note` a další

### Přidání nového jazyka

1. Zkopírovat složku `lang/cs/` jako `lang/{novy_kod}/`.
2. Přeložit hodnoty ve všech souborech.
3. Nastavit `APP_LOCALE={novy_kod}` v `.env` nebo přidat přepínač jazyka do UI.
