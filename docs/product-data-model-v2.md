# Product Data Model V2 (Laravel 10)

## Cile navrhu
- `products` obsahuje pouze sdilena data produktu.
- Typ produktu je oddelen v `product_types`.
- Technicke parametry jsou resene EAV vrstvou (`attributes`, `attribute_product_type`, `product_attribute_values`).
- Ceny jsou oddelene od produktu (`price_lists`, `product_prices`).
- Vyprodej je realizovan jako samostatny sklad (`warehouses.is_sale = true`) + zasoba v `product_stocks`.
- Vice obrazku zustava pres `product_images`.
- Dostupnost je oddelena od `products` pres `product_stocks`.
- Model je pripraven na import legacy dat (`legacy_*` sloupce v `products`, `legacy_payload` JSON).

## Databazova struktura

### 1) products (spolecna data)
- `id`
- `product_type_id` FK -> `product_types.id`
- `stock_item_id` FK -> `stock_items.id` (zachovana kompatibilita)
- `sku` (unique)
- `legacy_item_code` (index)
- `legacy_group_id` (index)
- `legacy_sphinx_id` (index)
- `name`
- `slug` (unique)
- `description`
- `legacy_payload` (json, nullable)
- `price`, `base_price_net` (zachovana kompatibilita)
- `active`, `is_active`, `visibility` (zachovana kompatibilita)
- `stock_qty` (zachovana kompatibilita)
- `created_at`, `updated_at`

### 2) product_types
- `id`
- `code` (unique) napriklad `battery`, `charger`, `adapter`
- `name`
- `description`
- `is_active`
- timestamps

### 3) attributes
- `id`
- `code` (unique) napriklad `capacity_mah`, `max_power_w`, `connector_type`
- `name`
- `data_type` (`text`, `number`, `boolean`, `json`)
- `unit` (napr. `mAh`, `V`, `W`)
- `is_filterable`
- `is_active`
- `sort_order`
- timestamps

### 4) attribute_product_type (vazba atributu na typ)
- `id`
- `attribute_id` FK -> `attributes.id`
- `product_type_id` FK -> `product_types.id`
- `is_required`
- `is_filterable`
- `sort_order`
- unique (`attribute_id`, `product_type_id`)
- timestamps

### 5) product_attribute_values (hodnoty atributu na produktu)
- `id`
- `product_id` FK -> `products.id`
- `attribute_id` FK -> `attributes.id`
- `value_text`
- `value_number`
- `value_boolean`
- `value_json`
- `value_unit`
- unique (`product_id`, `attribute_id`)
- timestamps

Poznamka: kazdy atribut vyuzije jen jeden sloupec hodnoty podle `attributes.data_type`.

### 6) price_lists (existujici)
- obchodni cenniky

### 7) product_prices (nove)
- `id`
- `product_id` FK -> `products.id`
- `price_list_id` FK -> `price_lists.id`
- `price_net`
- `price_gross` (optional)
- `valid_from`, `valid_to`
- unique (`product_id`, `price_list_id`, `valid_from`)
- timestamps

### 8) warehouses
- `id`
- `code` (unique), napr. `MAIN`, `SALE`, `BERLIN`
- `name`
- `is_sale` (true pro vyprodej)
- `is_active`
- `priority`
- timestamps

### 9) product_stocks
- `id`
- `product_id` FK -> `products.id`
- `warehouse_id` FK -> `warehouses.id`
- `quantity_on_hand`
- `quantity_reserved`
- `quantity_incoming`
- `backorderable`
- `available_from`
- unique (`product_id`, `warehouse_id`)
- timestamps

### 10) product_images (existujici)
- vice obrazku na produkt pres relaci `product_id` -> N obrazku

## Eloquent modely a relace
- `Product`:
  - `belongsTo(ProductType::class)`
  - `hasMany(ProductAttributeValue::class)`
  - `belongsToMany(ProductAttribute::class, 'product_attribute_values')`
  - `hasMany(ProductPrice::class)`
  - `hasMany(ProductStock::class)`
  - `hasMany(ProductImage::class)`
- `ProductType`:
  - `hasMany(Product::class)`
  - `belongsToMany(ProductAttribute::class, 'attribute_product_type')`
- `ProductAttribute`:
  - `belongsToMany(ProductType::class, 'attribute_product_type')`
  - `hasMany(ProductAttributeValue::class)`
- `ProductAttributeValue`: `belongsTo(Product::class)`, `belongsTo(ProductAttribute::class)`
- `PriceList`: `hasMany(ProductPrice::class)`
- `ProductPrice`: `belongsTo(Product::class)`, `belongsTo(PriceList::class)`
- `Warehouse`: `hasMany(ProductStock::class)`
- `ProductStock`: `belongsTo(Product::class)`, `belongsTo(Warehouse::class)`

## Mapovani legacy sloupcu -> novy model

| Legacy sloupec | Novy cil | Poznamka |
|---|---|---|
| ItemCode | `products.legacy_item_code`, `products.sku` | Pri importu lze pouzit jako primarni identifikator |
| ItemName | `products.name` |  |
| GrpId | `products.legacy_group_id` / mapovani na `categories` | Dle stare taxonomie |
| Interne | `products.legacy_payload->Interne` | Raw import |
| Ean | atribut `ean` nebo vlastni sloupec dle potreby | Doporuceno atribut `ean` jako text |
| Closed | `products.active/is_active` | Inverzni mapovani dle stare logiky |
| Volt | atribut `voltage_v` (`value_number`) | jednotka `V` |
| Kapacita | atribut `capacity_mah` (`value_number`) | jednotka `mAh` |
| Typ | `product_types.code` | Napr. `battery`, `charger`, `adapter` |
| Barva | atribut `color` (`value_text`) |  |
| Rozmer | atribut `dimensions` (`value_text`/`value_json`) |  |
| Obrazek | `product_images` | prvni obrazek |
| Obrazek2 | `product_images` | druhy obrazek |
| Dodavatel | atribut `supplier` (`value_text`) | nebo samostatna entita dodavatele |
| Vyrobce | atribut `manufacturer` (`value_text`) | nebo samostatna entita vyrobce |
| ModelGroup | atribut `model_group` (`value_text`) |  |
| ModelTyp | atribut `model_type` (`value_text`) |  |
| Hmotnost | atribut `weight_g`/`weight_kg` (`value_number`) | jednotka dle dat |
| Original | atribut `is_original` (`value_boolean`) |  |
| Startpage | atribut `startpage` (`value_boolean`) | nebo marketing flag |
| Berlin_Stav | `product_stocks.quantity_on_hand` pro sklad `BERLIN` |  |
| Berlin_Dni | atribut `lead_time_days` (`value_number`) |  |
| Berlin_Datum | `product_stocks.available_from` / atribut datum |  |
| KatalogoveCislo | atribut `catalog_number` (`value_text`) |  |
| Nadotaz | atribut `on_request` (`value_boolean`) |  |
| Cena1, Cena2, Cena5, Cena6, Cena7, Cena8 | `product_prices` + mapovani na `price_lists` | Napr. `DEFAULT`, `B2B`, `VIP` |
| Dispo | `product_stocks.quantity_on_hand` | dle definice zdroje |
| InfoText | `products.description` nebo atribut `info_text` |  |
| Plug | atribut `plug_type` (`value_text`) |  |
| Akce | sklad `SALE` (`warehouses.is_sale = true`) a/nebo price list promo | nepouzivat jen bool |
| Skupina | `categories` / atribut `group_label` |  |
| ShopExport | atribut `shop_export` (`value_boolean`) |  |
| Vyprodej | sklad `SALE` + `product_stocks` | oddelene od produktu |
| sphinx_id | `products.legacy_sphinx_id` | pro trasovatelnost |

## Priklad dat (battery)

```json
{
  "product_type": { "code": "battery", "name": "Baterie" },
  "product": {
    "sku": "BAT-18650-3000",
    "legacy_item_code": "A12345",
    "name": "Li-Ion 18650 3000mAh",
    "slug": "li-ion-18650-3000mah",
    "product_type_id": 1,
    "active": true
  },
  "attributes": [
    { "code": "capacity_mah", "data_type": "number", "value_number": 3000, "value_unit": "mAh" },
    { "code": "voltage_v", "data_type": "number", "value_number": 3.7, "value_unit": "V" },
    { "code": "chemistry", "data_type": "text", "value_text": "Li-Ion" }
  ],
  "prices": [
    { "price_list": "DEFAULT", "price_net": 119.00 },
    { "price_list": "B2B", "price_net": 99.00 }
  ],
  "stocks": [
    { "warehouse": "MAIN", "quantity_on_hand": 250, "quantity_reserved": 30 },
    { "warehouse": "SALE", "quantity_on_hand": 20, "quantity_reserved": 0 }
  ]
}
```

## Priklad dat (charger)

```json
{
  "product_type": { "code": "charger", "name": "Nabijecka" },
  "product": {
    "sku": "CHG-USB-C-65W",
    "legacy_item_code": "C77881",
    "name": "USB-C rychlonabijecka 65W",
    "slug": "usb-c-rychlonabijecka-65w",
    "product_type_id": 2,
    "active": true
  },
  "attributes": [
    { "code": "max_power_w", "data_type": "number", "value_number": 65, "value_unit": "W" },
    { "code": "connector_type", "data_type": "text", "value_text": "USB-C" },
    { "code": "supports_pd", "data_type": "boolean", "value_boolean": true }
  ],
  "prices": [
    { "price_list": "DEFAULT", "price_net": 549.00 }
  ],
  "stocks": [
    { "warehouse": "MAIN", "quantity_on_hand": 80, "quantity_reserved": 5 }
  ]
}
```

## Doporuceni pro administraci
- V admin formulare nejdriv vybrat `product_type`.
- Atributova sekce se renderuje dynamicky podle `attribute_product_type`.
- Pri ulozeni validovat hodnoty podle `attributes.data_type`.
- Nabidnout preset sablony atributu pro bezne typy (`battery`, `charger`, `adapter`).
- U skladu zobrazit radky po skladech (`warehouse`) a vypocet dostupnosti `on_hand - reserved`.
- U cen zobrazit timeline po cennicich (`valid_from`/`valid_to`).

## Doporuceni pro import
1. Nejdriv importovat ciselniky (`product_types`, `attributes`, `price_lists`, `warehouses`).
2. Potom import `products` (vkladat i `legacy_*`).
3. Nahrat `product_attribute_values` podle mapovani sloupcu.
4. Nahrat `product_prices` z `Cena1..Cena8` dle mapovaci tabulky cenniku.
5. Nahrat `product_stocks` po skladech (MAIN, BERLIN, SALE).
6. Nakonec importovat obrazky do `media_files` + vazby `product_images`.
7. Behem importu logovat neznama pole do `products.legacy_payload` kvuli dohledatelnosti.

## Poznamka ke kompatibilite
- Stavajici tabulky (`price_list_product`, `stock_items`, fallback sloupce v `products`) zustavaji zachovane.
- Nova vrstva muze bezet paralelne a migrace aplikovat postupne bez hard cutover.
