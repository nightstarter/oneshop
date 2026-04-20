<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Import\ImportReport;
use App\Services\Import\LegacyProductImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Import starých produktů, exModel a exTyp do nového flexibilního schématu.
 *
 * Spuštění (základní):
 *   php artisan import:legacy-products \
 *       --products=storage/import/products.csv \
 *       --models=storage/import/ex_models.csv \
 *       --types=storage/import/ex_types.csv
 *
 * Dry-run (nic nezapisuje do DB):
 *   php artisan import:legacy-products --products=... --dry-run
 *
 * SEO produkty:
 *   php artisan import:legacy-products --seo=storage/import/seo_products.csv
 *
 * Pouze jeden soubor:
 *   php artisan import:legacy-products --models=storage/import/ex_models.csv
 *
 * Přijímané formáty souborů: CSV (separator čárka nebo středník, UTF-8 nebo Windows-1250).
 * Hlavičkový řádek je povinný.
 *
 * Souborová struktura CSV:
 *   products.csv  – hlavičky: ItemCode,ItemName,GrpId,Interne,Ean,Closed,Volt,Kapacita,
 *                             Typ,Barva,Rozmer,Dodavatel,Vyrobce,ModelGroup,ModelTyp,
 *                             Hmotnost,Original,KatalogoveCislo,Nadotaz,
 *                             Cena1,Cena2,Cena5,Cena6,Cena7,Cena8,
 *                             Dispo,Berlin_Stav,Berlin_Dni,Berlin_Datum,
 *                             InfoText,Plug,Akce,Skupina,Vyprodej,sphinx_id
 *
 *   ex_models.csv – hlavičky: exID,exArtId,exModel
 *   ex_types.csv  – hlavičky: exID,exArtId,exTyp
 *   seo_products.csv – hlavičky: SeoSku,ParentItemCode,SeoName,SeoSlug,SeoDescription,LinkedModel
 */
class ImportLegacyProducts extends Command
{
    protected $signature = 'import:legacy-products
        {--products=       : Cesta k CSV souboru s produkty (relativní k base_path)}
        {--models=         : Cesta k CSV souboru s exModel daty}
        {--types=          : Cesta k CSV souboru s exTyp daty}
        {--seo=            : Cesta k CSV souboru s SEO produkty}
        {--separator=,     : Oddělovač sloupců v CSV (výchozí: čárka)}
        {--encoding=UTF-8  : Kódování souboru (UTF-8 nebo Windows-1250)}
        {--dry-run         : Pouze zpracování bez zápisu do databáze}
        {--errors-csv=     : Volitelná cesta pro export chyb do CSV}';

    protected $description = 'Importuje legacy produkty, modely zařízení a typová označení do nového schématu.';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('⚠  DRY-RUN: žádná data nebudou zapsána do databáze.');
        }

        $report  = new ImportReport();
        $service = new LegacyProductImportService($report);

        // ── 1. Nosné produkty ───────────────────────────────────────────
        if ($path = $this->option('products')) {
            $this->runImportPass(
                isDryRun: $isDryRun,
                callback: fn () => $this->processFile(
                    label:   'Nosné produkty',
                    path:    $path,
                    handler: static function (array $row) use ($service): void {
                        $service->importCarrierRow($row);
                    },
                    requiredHeaderKey: 'ItemCode',
                )
            );
        }

        // ── 2. SEO produkty ────────────────────────────────────────────
        if ($path = $this->option('seo')) {
            $this->runImportPass(
                isDryRun: $isDryRun,
                callback: fn () => $this->processFile(
                    label:   'SEO produkty',
                    path:    $path,
                    handler: static function (array $row) use ($service): void {
                        $service->importSeoRow($row);
                    },
                    requiredHeaderKey: 'SeoSku',
                )
            );
        }

        // ── 3. exModel vazby ───────────────────────────────────────────
        if ($path = $this->option('models')) {
            $this->runImportPass(
                isDryRun: $isDryRun,
                callback: fn () => $this->processFile(
                    label:   'exModel (modely zařízení)',
                    path:    $path,
                    handler: static function (array $row) use ($service): void {
                        $service->importDeviceModelRow($row);
                    },
                    requiredHeaderKey: 'exArtId',
                )
            );
        }

        // ── 4. exTyp vazby ─────────────────────────────────────────────
        if ($path = $this->option('types')) {
            $this->runImportPass(
                isDryRun: $isDryRun,
                callback: fn () => $this->processFile(
                    label:   'exTyp (typová označení)',
                    path:    $path,
                    handler: static function (array $row) use ($service): void {
                        $service->importPartNumberRow($row);
                    },
                    requiredHeaderKey: 'exArtId',
                )
            );
        }

        // ── Report ─────────────────────────────────────────────────────
        $this->printReport($report, $isDryRun);

        if ($errorsPath = $this->option('errors-csv')) {
            $this->exportErrorsCsv($report, $errorsPath);
        }

        return $report->hasErrors() ? self::FAILURE : self::SUCCESS;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Reads a CSV file line by line and calls $handler for each data row.
     */
    private function processFile(
        string $label,
        string $path,
        callable $handler,
        ?string $requiredHeaderKey = null,
    ): void
    {
        $fullPath = base_path(ltrim($path, '/\\'));

        if (! file_exists($fullPath)) {
            $this->error("Soubor nenalezen: {$fullPath}");
            return;
        }

        $this->info("Zpracovávám {$label}: {$fullPath}");

        $encoding  = strtoupper((string) $this->option('encoding'));
        $separator = (string) $this->option('separator') ?: ',';
        if ($separator === '\\t') {
            $separator = "\t";
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            $this->error("Nelze otevřít soubor: {$fullPath}");
            return;
        }

        // Read header row
        $rawHeader = fgetcsv($handle, 0, $separator);
        if ($rawHeader === false || $rawHeader === null) {
            $this->error("Prázdný soubor nebo neplatná hlavička: {$fullPath}");
            fclose($handle);
            return;
        }

        // Normalize encoding if needed
        $header = $this->normalizeEncoding($rawHeader, $encoding);

        if ($requiredHeaderKey !== null && ! in_array($requiredHeaderKey, $header, true)) {
            $hint = '';
            if (count($header) === 1 && str_contains((string) $header[0], ';') && $separator !== ';') {
                $hint = ' (pravděpodobně špatný --separator, zkus --separator=;)';
            }

            $this->error("Chybí povinná hlavička '{$requiredHeaderKey}' v souboru {$fullPath}{$hint}");
            fclose($handle);
            return;
        }

        $lineNum = 1;
        $bar     = null;

        // Count rows for progress bar (only for larger files)
        $totalLines = $this->estimateLines($fullPath);
        if ($totalLines > 50) {
            $bar = $this->output->createProgressBar($totalLines);
            $bar->start();
        }

        while (($row = fgetcsv($handle, 0, $separator)) !== false) {
            $lineNum++;
            if (array_filter($row, fn ($v) => trim($v) !== '') === []) {
                continue; // skip blank lines
            }

            $row = $this->normalizeEncoding($row, $encoding);

            // Combine header with row (handle ragged rows gracefully)
            $count = min(count($header), count($row));
            $data  = array_combine(array_slice($header, 0, $count), array_slice($row, 0, $count));

            $handler($data);

            $bar?->advance();
        }

        $bar?->finish();
        if ($bar) {
            $this->newLine();
        }

        fclose($handle);
        $this->line("  → {$label}: hotovo ({$lineNum} řádků).");
    }

    private function runImportPass(bool $isDryRun, callable $callback): void
    {
        if (! $isDryRun) {
            $callback();
            return;
        }

        DB::beginTransaction();
        try {
            $callback();
            DB::rollBack();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function printReport(ImportReport $report, bool $isDryRun): void
    {
        $this->newLine();
        $this->line($isDryRun
            ? '<fg=yellow>═══ REPORT (DRY-RUN) ═══</>'
            : '<fg=green>═══ REPORT ═══</>');

        foreach ($report->summaryLines() as $line) {
            $this->line("  {$line}");
        }

        if ($report->hasErrors()) {
            $this->newLine();
            $this->error("Chyby ({$report->errorCount()}):");
            $headers = ['SKU / zdroj', 'Důvod'];
            $rows    = array_map(
                fn ($e) => [$e['sku'], mb_strimwidth($e['reason'], 0, 120, '…')],
                $report->errors(),
            );
            $this->table($headers, $rows);
        }
    }

    private function exportErrorsCsv(ImportReport $report, string $path): void
    {
        if (! $report->hasErrors()) {
            return;
        }

        $fullPath = base_path(ltrim($path, '/\\'));
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $handle = fopen($fullPath, 'w');
        fputcsv($handle, ['sku', 'reason']);
        foreach ($report->errors() as $e) {
            fputcsv($handle, [$e['sku'], $e['reason']]);
        }
        fclose($handle);

        $this->info("Chyby exportovány: {$fullPath}");
    }

    /** @param list<string> $values */
    private function normalizeEncoding(array $values, string $encoding): array
    {
        if ($encoding === 'UTF-8') {
            return $values;
        }
        return array_map(
            fn ($v) => mb_convert_encoding((string) $v, 'UTF-8', $encoding),
            $values,
        );
    }

    private function estimateLines(string $path): int
    {
        $count = 0;
        $fh    = fopen($path, 'r');
        while (! feof($fh)) {
            fgets($fh);
            $count++;
        }
        fclose($fh);
        return max(0, $count - 1); // subtract header
    }
}
