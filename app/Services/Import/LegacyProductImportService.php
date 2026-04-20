<?php

namespace App\Services\Import;

use App\Models\DeviceModel;
use App\Models\MediaFile;
use App\Models\PartNumber;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductType;
use App\Models\Warehouse;
use App\Support\SearchNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Imports legacy products (nosné i SEO), kompatibilní modely zařízení,
 * typová označení, ceny a sklady z normalizovaných CSV řádků.
 *
 * Každá veřejná metoda je idempotentní (updateOrCreate) a přispívá
 * do sdíleného ImportReport objektu.
 *
 * ── Mapování legacy sloupců ─────────────────────────────────────────────
 *
 * products CSV row keys:
 *   ItemCode, ItemName, GrpId, Interne, Ean, Closed, Volt, Kapacita,
 *   Typ, Barva, Rozmer, Dodavatel, Vyrobce, ModelGroup, ModelTyp,
 *   Hmotnost, Original, KatalogoveCislo, Nadotaz,
 *   Cena1, Cena2, Cena5, Cena6, Cena7, Cena8,
 *   Dispo, Berlin_Stav, Berlin_Dni, InfoText, Plug, Akce, Vyprodej,
 *   sphinx_id
 *
 * exModel CSV row keys: exID, exArtId, exModel
 * exTyp   CSV row keys: exID, exArtId, exTyp
 *
 * SEO product CSV row keys:
 *   SeoSku, ParentItemCode, SeoName, SeoSlug, SeoDescription,
 *   LinkedModel  (optional: model_name to link via linked_device_model_id)
 */
class LegacyProductImportService
{
    /**
     * Legacy price-list column → price_list code mapping.
     * Extend if additional Cena* columns exist.
     */
    private const PRICE_MAP = [
        'Cena1' => 'DEFAULT',
        'Cena2' => 'B2B',
        'Cena5' => 'VIP',
        'Cena6' => 'RESELLER',
        'Cena7' => 'BULK',
        'Cena8' => 'PARTNER',
    ];

    /** Attribute code → legacy column mapping for numeric/text fields. */
    private const ATTR_MAP = [
        'capacity_mah'   => ['col' => 'Kapacita',       'type' => 'number'],
        'voltage_v'      => ['col' => 'Volt',            'type' => 'number'],
        'chemistry'      => ['col' => 'Typ',             'type' => 'text'],
        'plug_type'      => ['col' => 'Plug',            'type' => 'text'],
        'color'          => ['col' => 'Barva',           'type' => 'text'],
        'weight_g'       => ['col' => 'Hmotnost',        'type' => 'number'],
        'catalog_number' => ['col' => 'KatalogoveCislo', 'type' => 'text'],
        'manufacturer'   => ['col' => 'Vyrobce',         'type' => 'text'],
        'model_group'    => ['col' => 'ModelGroup',      'type' => 'text'],
        'model_type'     => ['col' => 'ModelTyp',        'type' => 'text'],
        'lead_time_days' => ['col' => 'Berlin_Dni',      'type' => 'number'],
        'ean'            => ['col' => 'Ean',             'type' => 'text'],
    ];

    /** Boolean attribute code → legacy column */
    private const BOOL_ATTR_MAP = [
        'is_original' => 'Original',
        'on_request'  => 'Nadotaz',
    ];

    // Cached lookups to avoid N+1 queries during a single run
    private ?array $priceLists  = null;
    private ?array $warehouses  = null;
    private ?array $productTypes = null;

    public function __construct(
        private readonly ImportReport $report,
    ) {}

    // ── Public API ────────────────────────────────────────────────────────

    /**
     * Import one nosný (carrier) product row from the legacy products table.
     *
     * @param array<string,mixed> $row CSV/DB row keyed by legacy column names
     */
    public function importCarrierRow(array $row): void
    {
        $sku = trim((string) ($row['ItemCode'] ?? ''));
        if ($sku === '') {
            $this->report->incSkipped();
            return;
        }

        try {
            DB::transaction(function () use ($sku, $row) {
                $isNew    = ! Product::where('legacy_item_code', $sku)->exists();
                $active   = ! $this->truthy($row['Closed'] ?? false);
                $typeCode = $this->resolveTypeCode($row);
                $typeId   = $typeCode ? $this->productTypes()[$typeCode] ?? null : null;

                $product = Product::updateOrCreate(
                    ['legacy_item_code' => $sku],
                    [
                        'product_type_id'   => $typeId,
                        'sku'               => $sku,
                        'name'              => trim($row['ItemName'] ?? $sku),
                        'slug'              => $this->makeSlug($sku, $row['ItemName'] ?? ''),
                        'description'       => trim($row['InfoText'] ?? ''),
                        'base_price_net'    => $this->decimal($row['Cena1'] ?? 0),
                        'price'             => $this->decimal($row['Cena1'] ?? 0),
                        'active'            => $active,
                        'is_active'         => $active,
                        'legacy_group_id'   => $row['GrpId'] ?? null,
                        'legacy_sphinx_id'  => isset($row['sphinx_id']) && $row['sphinx_id'] !== '' ? (int) $row['sphinx_id'] : null,
                        'legacy_payload'    => $this->buildLegacyPayload($row),
                        'parent_product_id' => null,
                    ]
                );

                $isNew ? $this->report->incCreated() : $this->report->incUpdated();

                $this->syncAttributes($product, $row);
                $this->syncPrices($product, $row);
                $this->syncStocks($product, $row);
                $this->syncImages($product, $row);
            });
        } catch (Throwable $e) {
            $this->report->addError($sku, $e->getMessage());
        }
    }

    /**
     * Import one SEO product row.
     *
     * Expected keys: SeoSku, ParentItemCode, SeoName, SeoSlug,
     *                SeoDescription, LinkedModel (optional)
     */
    public function importSeoRow(array $row): void
    {
        $seoSku         = trim((string) ($row['SeoSku'] ?? ''));
        $parentItemCode = trim((string) ($row['ParentItemCode'] ?? ''));

        if ($seoSku === '' || $parentItemCode === '') {
            $this->report->incSkipped();
            return;
        }

        try {
            DB::transaction(function () use ($seoSku, $parentItemCode, $row) {
                $parent = Product::where('legacy_item_code', $parentItemCode)
                    ->whereNull('parent_product_id')
                    ->first();

                if (! $parent) {
                    $this->report->addError($seoSku, "Nosný produkt '{$parentItemCode}' nenalezen.");
                    return;
                }

                $linkedModelId = null;
                if (! empty($row['LinkedModel'])) {
                    $linkedModelId = DeviceModel::where('model_name', trim($row['LinkedModel']))->value('id');
                }

                $isNew = ! Product::where('sku', $seoSku)->exists();

                Product::updateOrCreate(
                    ['sku' => $seoSku],
                    [
                        'parent_product_id'      => $parent->id,
                        'linked_device_model_id' => $linkedModelId,
                        'name'                   => trim($row['SeoName'] ?? $parent->name),
                        'slug'                   => $this->makeSlug($seoSku, $row['SeoSlug'] ?? $row['SeoName'] ?? ''),
                        'description'            => trim($row['SeoDescription'] ?? ''),
                        'base_price_net'         => $parent->base_price_net,
                        'price'                  => $parent->price,
                        'active'                 => $parent->active,
                        'is_active'              => $parent->is_active,
                        'product_type_id'        => $parent->product_type_id,
                    ]
                );

                $isNew ? $this->report->incSeoCreated() : $this->report->incSeoUpdated();
            });
        } catch (Throwable $e) {
            $this->report->addError($seoSku, $e->getMessage());
        }
    }

    /**
     * Import a row from the legacy exModel table.
     * Upserts device_model and links it to the carrier product.
     *
     * Expected keys: exID, exArtId, exModel
     */
    public function importDeviceModelRow(array $row): void
    {
        $artId     = trim((string) ($row['exArtId'] ?? ''));
        $modelName = trim((string) ($row['exModel'] ?? ''));

        if ($artId === '' || $modelName === '') {
            $this->report->incSkipped();
            return;
        }

        try {
            $normalized = SearchNormalizer::normalize($modelName);

            $dm = DeviceModel::updateOrCreate(
                ['model_name' => $modelName],
                [
                    'model_normalized' => $normalized,
                    'slug'             => $this->makeSlug($modelName, $modelName),
                    'legacy_ex_id'     => $row['exID'] ?? null,
                    'legacy_art_id'    => $artId,
                    'active'           => true,
                ]
            );

            // Link to the carrier product identified by ItemCode / legacy_item_code
            $product = Product::where('legacy_item_code', $artId)
                ->whereNull('parent_product_id')
                ->first();

            if (! $product) {
                $this->report->addError(
                    "exModel/{$artId}",
                    "Nosný produkt s ItemCode '{$artId}' nenalezen."
                );
                return;
            }

            $product->deviceModels()->syncWithoutDetaching([$dm->id]);
            $this->report->incModelLinks();
        } catch (Throwable $e) {
            $this->report->addError("exModel/{$artId}", $e->getMessage());
        }
    }

    /**
     * Import a row from the legacy exTyp table.
     * Upserts part_number and links it to the carrier product.
     *
     * Expected keys: exID, exArtId, exTyp
     */
    public function importPartNumberRow(array $row): void
    {
        $artId = trim((string) ($row['exArtId'] ?? ''));
        $typ   = trim((string) ($row['exTyp'] ?? ''));

        if ($artId === '' || $typ === '') {
            $this->report->incSkipped();
            return;
        }

        try {
            $normalized = SearchNormalizer::normalize($typ);

            $pn = PartNumber::updateOrCreate(
                ['value' => $typ],
                [
                    'value_normalized' => $normalized,
                    'legacy_ex_id'     => $row['exID'] ?? null,
                    'legacy_art_id'    => $artId,
                    'active'           => true,
                ]
            );

            $product = Product::where('legacy_item_code', $artId)
                ->whereNull('parent_product_id')
                ->first();

            if (! $product) {
                $this->report->addError(
                    "exTyp/{$artId}",
                    "Nosný produkt s ItemCode '{$artId}' nenalezen."
                );
                return;
            }

            $product->partNumbers()->syncWithoutDetaching([$pn->id]);
            $this->report->incPartNumLinks();
        } catch (Throwable $e) {
            $this->report->addError("exTyp/{$artId}", $e->getMessage());
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function syncAttributes(Product $product, array $row): void
    {
        foreach (self::ATTR_MAP as $code => $spec) {
            $raw = $row[$spec['col']] ?? '';
            if ((string) $raw === '') {
                continue;
            }

            $attrId = $this->attrId($code);
            if ($attrId === null) {
                continue; // attribute not seeded yet — skip silently
            }

            if ($spec['type'] === 'number') {
                $val = $this->decimal($raw);
                if ($val <= 0) {
                    continue;
                }
                ProductAttributeValue::updateOrCreate(
                    ['product_id' => $product->id, 'attribute_id' => $attrId],
                    ['value_number' => $val, 'value_text' => null, 'value_boolean' => null],
                );
            } else {
                ProductAttributeValue::updateOrCreate(
                    ['product_id' => $product->id, 'attribute_id' => $attrId],
                    ['value_text' => trim((string) $raw), 'value_number' => null, 'value_boolean' => null],
                );
            }
        }

        foreach (self::BOOL_ATTR_MAP as $code => $col) {
            $raw = $row[$col] ?? null;
            if ($raw === null || (string) $raw === '') {
                continue;
            }
            $attrId = $this->attrId($code);
            if ($attrId === null) {
                continue; // attribute not seeded yet — skip silently
            }
            ProductAttributeValue::updateOrCreate(
                ['product_id' => $product->id, 'attribute_id' => $attrId],
                ['value_boolean' => $this->truthy($raw), 'value_text' => null, 'value_number' => null],
            );
        }
    }

    private function syncPrices(Product $product, array $row): void
    {
        $priceLists = $this->priceLists();
        $count = 0;

        foreach (self::PRICE_MAP as $col => $code) {
            $raw = $row[$col] ?? '';
            if ((string) $raw === '') {
                continue;
            }
            $net = $this->decimal($raw);
            if ($net <= 0) {
                continue;
            }

            $plId = $priceLists[$code] ?? null;
            if (! $plId) {
                continue;
            }

            ProductPrice::updateOrCreate(
                ['product_id' => $product->id, 'price_list_id' => $plId, 'valid_from' => null],
                ['price_net' => $net],
            );
            $count++;
        }

        $this->report->incPriceRows($count);
    }

    private function syncImages(Product $product, array $row): void
    {
        // Obrazek  → primary image filename
        // Obrazek2 → additional images, comma-separated filenames
        $primary    = trim((string) ($row['Obrazek']  ?? ''));
        $additional = trim((string) ($row['Obrazek2'] ?? ''));

        $filenames = [];
        if ($primary !== '') {
            $filenames[] = [$primary, true];
        }
        foreach (array_filter(array_map('trim', explode(',', $additional))) as $name) {
            $filenames[] = [$name, false];
        }

        if ($filenames === []) {
            return;
        }

        $diskRoot = rtrim((string) config('filesystems.disks.private_products.root'), '/\\');
        $sortOrder = (int) ($product->productImages()->max('sort_order') ?? 0);
        $linked    = 0;

        foreach ($filenames as [$filename, $isPrimary]) {
            // Search the disk root (flat and one level deep) for the filename
            $absolutePath = $this->findImageOnDisk($diskRoot, $filename);
            if ($absolutePath === null) {
                $this->report->addError(
                    $product->sku,
                    "Obrázek '{$filename}' nenalezen na disku."
                );
                continue;
            }

            $checksum  = hash_file('sha256', $absolutePath);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimeMap   = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',  'gif'  => 'image/gif',
                'webp' => 'image/webp',
            ];

            $relativePath = ltrim(str_replace(['\\', $diskRoot], ['/', ''], $absolutePath), '/');

            $mediaFile = MediaFile::firstOrCreate(
                ['checksum' => $checksum],
                [
                    'disk'          => 'private_products',
                    'path'          => $relativePath,
                    'original_name' => $filename,
                    'mime_type'     => $mimeMap[$extension] ?? 'image/jpeg',
                    'extension'     => $extension,
                    'size'          => filesize($absolutePath) ?: 0,
                    'width'         => null,
                    'height'        => null,
                ]
            );

            $alreadyLinked = ProductImage::where('product_id', $product->id)
                ->where('media_file_id', $mediaFile->id)
                ->exists();

            if (! $alreadyLinked) {
                $sortOrder += 10;
                ProductImage::create([
                    'product_id'    => $product->id,
                    'media_file_id' => $mediaFile->id,
                    'sort_order'    => $sortOrder,
                    'alt'           => $product->name,
                    'is_primary'    => $isPrimary && ! $product->productImages()
                                            ->where('is_primary', true)
                                            ->exists(),
                ]);
                $linked++;
            }
        }

        if ($linked > 0) {
            $this->report->incImageLinks($linked);
        }
    }

    /**
     * Searches for $filename directly in $root and one subdirectory level.
     * Returns absolute path or null if not found.
     */
    private function findImageOnDisk(string $root, string $filename): ?string
    {
        $direct = $root . DIRECTORY_SEPARATOR . $filename;
        if (is_file($direct)) {
            return $direct;
        }

        // One level of subdirectories (e.g. year/month structure)
        if (is_dir($root)) {
            foreach (new \FilesystemIterator($root, \FilesystemIterator::SKIP_DOTS) as $item) {
                if ($item->isDir()) {
                    $candidate = $item->getPathname() . DIRECTORY_SEPARATOR . $filename;
                    if (is_file($candidate)) {
                        return $candidate;
                    }
                }
            }
        }

        return null;
    }

    private function syncStocks(Product $product, array $row): void
    {
        $warehouses = $this->warehouses();
        $count = 0;

        // Main stock (Dispo column)
        if (isset($warehouses['MAIN'])) {
            $qty = (int) ($row['Dispo'] ?? 0);
            ProductStock::updateOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouses['MAIN']],
                ['quantity_on_hand' => $qty, 'quantity_reserved' => 0],
            );
            $count++;
        }

        // Berlin stock
        if (isset($warehouses['BERLIN'])) {
            $qty = (int) ($row['Berlin_Stav'] ?? 0);
            $availFrom = null;
            if (! empty($row['Berlin_Datum'])) {
                try {
                    $availFrom = \Carbon\Carbon::parse($row['Berlin_Datum'])->toDateTimeString();
                } catch (\Throwable) {
                    // unparsable – leave null
                }
            }
            ProductStock::updateOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouses['BERLIN']],
                ['quantity_on_hand' => $qty, 'quantity_reserved' => 0, 'available_from' => $availFrom],
            );
            $count++;
        }

        // Clearance (Vyprodej column: positive int = qty in sale warehouse)
        if (isset($warehouses['SALE'])) {
            $saleQty = (int) ($row['Vyprodej'] ?? 0);
            if ($saleQty > 0) {
                ProductStock::updateOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $warehouses['SALE']],
                    ['quantity_on_hand' => $saleQty, 'quantity_reserved' => 0],
                );
                $count++;
            }
        }

        $this->report->incStockRows($count);
    }

    // ── Lookup caches ─────────────────────────────────────────────────────

    /** @return array<string,int> code → id */
    private function priceLists(): array
    {
        return $this->priceLists ??= PriceList::pluck('id', 'code')->all();
    }

    /** @return array<string,int> code → id */
    private function warehouses(): array
    {
        return $this->warehouses ??= Warehouse::pluck('id', 'code')->all();
    }

    /** @return array<string,int> code → id */
    private function productTypes(): array
    {
        return $this->productTypes ??= ProductType::pluck('id', 'code')->all();
    }

    /** Lazy attribute-id cache (fetches on first access per code). */
    private array $attrIds = [];

    private function attrId(string $code): ?int
    {
        if (! array_key_exists($code, $this->attrIds)) {
            $this->attrIds[$code] = \App\Models\ProductAttribute::where('code', $code)->value('id');
        }
        return $this->attrIds[$code];
    }

    // ── Utility ───────────────────────────────────────────────────────────

    private function truthy(mixed $v): bool
    {
        if (is_bool($v)) {
            return $v;
        }
        return in_array(strtolower((string) $v), ['1', 'true', 'yes', 'ano', 'y'], true);
    }

    private function decimal(mixed $v): float
    {
        // Handle comma as decimal separator (Czech locale exports)
        $str = str_replace(',', '.', (string) $v);
        return (float) $str;
    }

    private function makeSlug(string $key, string $name): string
    {
        $base = Str::slug($name ?: $key);
        if ($base === '') {
            $base = Str::slug($key);
        }

        // Ensure uniqueness: if taken by a different product, append itemCode suffix
        $candidate = $base;
        $i = 1;
        while (
            Product::where('slug', $candidate)
                ->where('legacy_item_code', '!=', $key)
                ->where('sku', '!=', $key)
                ->exists()
        ) {
            $candidate = $base . '-' . $i++;
        }

        return $candidate;
    }

    private function buildLegacyPayload(array $row): array
    {
        // Capture rarely used fields as JSON for traceability
        return array_filter([
            'GrpId'    => $row['GrpId']    ?? null,
            'Interne'  => $row['Interne']  ?? null,
            'Skupina'  => $row['Skupina']  ?? null,
            'Akce'     => $row['Akce']     ?? null,
            'Dodavatel'=> $row['Dodavatel'] ?? null,
            'Rozmer'   => $row['Rozmer']   ?? null,
        ], fn ($v) => $v !== null && (string) $v !== '');
    }

    /**
     * Product type inference rules for legacy import:
     * - If ItemName contains "nabijecka" / "charger" => charger
     * - Else if Typ has any non-empty value => battery (Typ is battery chemistry)
     * - Else => unknown/null
     *
     * @param array<string,mixed> $row
     */
    private function resolveTypeCode(array $row): ?string
    {
        $name = trim((string) ($row['ItemName'] ?? ''));
        $typ = trim((string) ($row['Typ'] ?? ''));

        $nameAscii = mb_strtolower(Str::ascii($name));

        if (str_contains($nameAscii, 'nabijecka') || str_contains($nameAscii, 'charger')) {
            return 'charger';
        }

        if ($typ !== '') {
            return 'battery';
        }

        return null;
    }
}
