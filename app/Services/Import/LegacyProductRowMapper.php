<?php

namespace App\Services\Import;

use App\Models\Product;
use Illuminate\Support\Str;

class LegacyProductRowMapper
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function mapCarrierProductAttributes(array $row, ?int $productTypeId): array
    {
        $sku = trim((string) ($row['ItemCode'] ?? ''));
        $name = trim((string) ($row['ItemName'] ?? $sku));

        return [
            'product_type_id' => $productTypeId,
            'sku' => $sku,
            'name' => $name,
            'slug' => $this->makeSlug($sku, $row['ItemName'] ?? ''),
            'description' => trim((string) ($row['InfoText'] ?? '')),
            'price' => $this->mapNetPrice($row['Cena1'] ?? 0),
            'active' => ! $this->truthy($row['Closed'] ?? false),
            'legacy_group_id' => $row['GrpId'] ?? null,
            'legacy_sphinx_id' => isset($row['sphinx_id']) && $row['sphinx_id'] !== '' ? (int) $row['sphinx_id'] : null,
            'legacy_payload' => $this->buildLegacyPayload($row),
            'parent_product_id' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function mapSeoProductAttributes(array $row, Product $parent, ?int $linkedModelId): array
    {
        $seoSku = trim((string) ($row['SeoSku'] ?? ''));

        return [
            'parent_product_id' => $parent->id,
            'linked_device_model_id' => $linkedModelId,
            'name' => trim((string) ($row['SeoName'] ?? $parent->name)),
            'slug' => $this->makeSlug($seoSku, $row['SeoSlug'] ?? $row['SeoName'] ?? ''),
            'description' => trim((string) ($row['SeoDescription'] ?? '')),
            'price' => $parent->price,
            'active' => $parent->active,
            'stock_item_id' => $parent->stock_item_id,
            'product_type_id' => $parent->product_type_id,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function buildLegacyPayload(array $row): array
    {
        return array_filter([
            'GrpId' => $row['GrpId'] ?? null,
            'Interne' => $row['Interne'] ?? null,
            'Skupina' => $row['Skupina'] ?? null,
            'Akce' => $row['Akce'] ?? null,
            'Dodavatel' => $row['Dodavatel'] ?? null,
            'Rozmer' => $row['Rozmer'] ?? null,
        ], fn ($value) => $value !== null && (string) $value !== '');
    }

    public function mapNetPrice(mixed $value): float
    {
        return $this->decimal($value) / (1 + ((float) config('shop.vat_rate', 21.0) / 100));
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'ano', 'y'], true);
    }

    private function decimal(mixed $value): float
    {
        return (float) str_replace(',', '.', (string) $value);
    }

    private function makeSlug(string $key, string $name): string
    {
        $base = Str::slug($name ?: $key);
        if ($base === '') {
            $base = Str::slug($key);
        }

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
}
