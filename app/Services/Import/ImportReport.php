<?php

namespace App\Services\Import;

/**
 * Immutable report returned by LegacyProductImportService.
 * Accumulates counts and error log during import.
 */
final class ImportReport
{
    private int   $created       = 0;
    private int   $updated       = 0;
    private int   $seoCreated    = 0;
    private int   $seoUpdated    = 0;
    private int   $modelLinks    = 0;
    private int   $partNumLinks  = 0;
    private int   $priceRows     = 0;
    private int   $stockRows     = 0;
    private int   $imageLinks    = 0;
    private int   $skipped       = 0;
    /** @var list<array{sku: string, reason: string}> */
    private array $errors        = [];

    // ── Incrementers ──────────────────────────────────────────────────────

    public function incCreated(): void      { $this->created++; }
    public function incUpdated(): void      { $this->updated++; }
    public function incSeoCreated(): void   { $this->seoCreated++; }
    public function incSeoUpdated(): void   { $this->seoUpdated++; }
    public function incModelLinks(int $n = 1): void   { $this->modelLinks   += $n; }
    public function incPartNumLinks(int $n = 1): void { $this->partNumLinks += $n; }
    public function incPriceRows(int $n = 1): void    { $this->priceRows    += $n; }
    public function incStockRows(int $n = 1): void    { $this->stockRows    += $n; }
    public function incImageLinks(int $n = 1): void   { $this->imageLinks   += $n; }
    public function incSkipped(): void { $this->skipped++; }

    public function addError(string $sku, string $reason): void
    {
        $this->errors[] = ['sku' => $sku, 'reason' => $reason];
    }

    // ── Readers ───────────────────────────────────────────────────────────

    public function created(): int      { return $this->created; }
    public function updated(): int      { return $this->updated; }
    public function seoCreated(): int   { return $this->seoCreated; }
    public function seoUpdated(): int   { return $this->seoUpdated; }
    public function modelLinks(): int   { return $this->modelLinks; }
    public function partNumLinks(): int { return $this->partNumLinks; }
    public function priceRows(): int    { return $this->priceRows; }
    public function stockRows(): int    { return $this->stockRows; }
    public function imageLinks(): int   { return $this->imageLinks; }
    public function skipped(): int      { return $this->skipped; }
    public function errorCount(): int   { return count($this->errors); }

    /** @return list<array{sku: string, reason: string}> */
    public function errors(): array { return $this->errors; }

    public function hasErrors(): bool { return count($this->errors) > 0; }

    /**
     * Human-readable summary lines.
     *
     * @return list<string>
     */
    public function summaryLines(): array
    {
        return [
            "Nosné produkty       : vytvoření {$this->created}  / aktualizace {$this->updated}",
            "SEO produkty         : vytvoření {$this->seoCreated} / aktualizace {$this->seoUpdated}",
            "Vazby modelů zařízení: {$this->modelLinks}",
            "Vazby typových označení: {$this->partNumLinks}",
            "Řádky cen            : {$this->priceRows}",
            "Řádky skladů         : {$this->stockRows}",
            "Napojené obrázky     : {$this->imageLinks}",
            "Přeskočeno (prázdné) : {$this->skipped}",
            "Chyby                : {$this->errorCount()}",
        ];
    }
}
