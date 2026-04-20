<?php

namespace App\Console\Commands;

use App\Models\MediaFile;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use FilesystemIterator;
use RuntimeException;

/**
 * Bulk-importuje obrázky z disku private_products do tabulky media_files.
 *
 * Spuštění (zaregistruje všechny dosud nezaevidované soubory):
 *   php artisan import:product-images
 *
 * Dry-run (žádné zápisy):
 *   php artisan import:product-images --dry-run
 *
 * Automatické napojení na produkty dle SKU prefixu názvu souboru:
 *   php artisan import:product-images --link-by-sku
 *
 *   Konvence pojmenování: soubor se napojí na produkt jehož SKU je prefixem
 *   názvu souboru (bez adresáře). Příklady:
 *     BAT-001.jpg         -> SKU "BAT-001"
 *     BAT-001-front.jpg   -> SKU "BAT-001"
 *     BAT-001_2.jpg       -> SKU "BAT-001"
 *
 *   Separátorem je první výskyt znaku -, _, mezery nebo tečky za SKU.
 *   Pokud je celý název souboru (bez přípony) shodný se SKU, napojí se taky.
 *
 * Preskoceni souborů které jsou již v media_files:
 *   Detekce probíhá na základě sha256 checksum — import je idempotentní.
 *
 * Skenovani konkretního podadresáře:
 *   php artisan import:product-images --subdir=2024/01
 */
class ImportProductImages extends Command
{
    protected $signature = 'import:product-images
        {--subdir=   : Volitelný podadresář na disku private_products}
        {--link-by-sku : Automaticky napojit soubory na produkty dle SKU prefixu názvu}
        {--dry-run   : Pouze analýza, žádný zápis do DB ani disku}';

    protected $description = 'Zaregistruje existující soubory z disku private_products do tabulky media_files.';

    private const MIME_MAP = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
    ];

    public function handle(): int
    {
        $isDryRun  = (bool) $this->option('dry-run');
        $linkBySku = (bool) $this->option('link-by-sku');
        $subdir    = (string) ($this->option('subdir') ?? '');

        if ($isDryRun) {
            $this->warn('DRY-RUN: žádné zápisy do databáze.');
        }

        // Storage::allFiles() on local disk disallows symbolic links by default.
        // We use an explicit scanner so imports can traverse symlinked directories.
        $files = $this->collectFilesFollowSymlinks($subdir ?: '');

        // Only image files
        $imageFiles = array_values(array_filter(
            $files,
            fn ($path) => isset(self::MIME_MAP[strtolower(pathinfo($path, PATHINFO_EXTENSION))])
        ));

        $total    = count($imageFiles);
        $imported = 0;
        $skipped  = 0;
        $linked   = 0;
        $errors   = [];

        $this->info("Nalezeno souborů na disku: {$total}");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($imageFiles as $relativePath) {
            $bar->advance();

            try {
                $absolutePath = $this->absolutePath($relativePath);
                $checksum     = hash_file('sha256', $absolutePath);

                $existing = MediaFile::where('checksum', $checksum)->first();

                if ($existing !== null) {
                    $skipped++;

                    if ($linkBySku && ! $isDryRun) {
                        $linked += $this->linkToProduct($existing);
                    }
                    continue;
                }

                if ($isDryRun) {
                    $imported++;
                    continue;
                }

                $mediaFile = DB::transaction(function () use ($relativePath, $absolutePath, $checksum) {
                    [$width, $height] = $this->detectDimensions($absolutePath);
                    $extension        = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

                    return MediaFile::create([
                        'disk'          => 'private_products',
                        'path'          => $relativePath,
                        'original_name' => basename($relativePath),
                        'mime_type'     => self::MIME_MAP[$extension] ?? 'image/jpeg',
                        'extension'     => $extension,
                        'size'          => filesize($absolutePath) ?: 0,
                        'checksum'      => $checksum,
                        'width'         => $width,
                        'height'        => $height,
                    ]);
                });

                $imported++;

                if ($linkBySku) {
                    $linked += $this->linkToProduct($mediaFile);
                }
            } catch (\Throwable $e) {
                $errors[] = [$relativePath, mb_strimwidth($e->getMessage(), 0, 100, '…')];
            }
        }

        $bar->finish();
        $this->newLine(2);

        // ── Report ────────────────────────────────────────────────────────
        $this->line('<fg=green>═══ REPORT ═══</>');
        $this->line("  Celkem souborů      : {$total}");
        $this->line("  Nově zaregistrováno : {$imported}");
        $this->line("  Přeskočeno (již v DB): {$skipped}");

        if ($linkBySku) {
            $this->line("  Napojeno na produkty : {$linked}");
        }

        if ($errors) {
            $this->newLine();
            $this->error('Chyby (' . count($errors) . '):');
            $this->table(['Soubor', 'Chyba'], $errors);
        }

        return $errors ? self::FAILURE : self::SUCCESS;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Links the media file to a product if the filename starts with an existing SKU.
     * Returns 1 if a new product_images row was created, 0 otherwise.
     */
    private function linkToProduct(MediaFile $mediaFile): int
    {
        $basename = pathinfo($mediaFile->original_name ?? $mediaFile->path, PATHINFO_FILENAME);

        // Longest SKU wins: exact match, or SKU followed by a known separator.
        $product = Product::whereRaw(
            '? = sku
            OR ? LIKE CONCAT(sku, "-%")
            OR ? LIKE CONCAT(sku, "_%")
            OR ? LIKE CONCAT(sku, " %")
            OR ? LIKE CONCAT(sku, ".%")',
            [$basename, $basename, $basename, $basename, $basename]
        )
            ->orderByRaw('LENGTH(sku) DESC')
            ->first();

        if ($product === null) {
            return 0;
        }

        // Skip if already linked
        if (ProductImage::where('product_id', $product->id)
            ->where('media_file_id', $mediaFile->id)
            ->exists()) {
            return 0;
        }

        $nextOrder  = (int) ($product->productImages()->max('sort_order') ?? 0) + 10;
        $hasPrimary = $product->productImages()->where('is_primary', true)->exists();

        ProductImage::create([
            'product_id'    => $product->id,
            'media_file_id' => $mediaFile->id,
            'sort_order'    => $nextOrder,
            'alt'           => $product->name,
            'is_primary'    => ! $hasPrimary,
        ]);

        return 1;
    }

    private function absolutePath(string $relativePath): string
    {
        $root = config('filesystems.disks.private_products.root');
        return rtrim($root, '/\\') . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');
    }

    private function detectDimensions(string $absolutePath): array
    {
        if (! function_exists('getimagesize')) {
            return [null, null];
        }
        $info = @getimagesize($absolutePath);
        return $info ? [$info[0], $info[1]] : [null, null];
    }

    /**
     * Returns file paths relative to private_products root.
     * Traverses symlinked directories and prevents infinite loops with realpath tracking.
     */
    private function collectFilesFollowSymlinks(string $subdir = ''): array
    {
        $root = rtrim((string) config('filesystems.disks.private_products.root'), '/\\');
        if ($root === '' || ! is_dir($root)) {
            throw new RuntimeException('Disk private_products root neexistuje nebo neni adresar: ' . $root);
        }

        $start = $subdir !== ''
            ? $root . DIRECTORY_SEPARATOR . ltrim($subdir, '/\\')
            : $root;

        if (! is_dir($start)) {
            throw new RuntimeException('Subdir neexistuje: ' . $start);
        }

        $queue = [$start];
        $seenRealDirs = [];
        $files = [];

        while ($dir = array_pop($queue)) {
            $realDir = realpath($dir) ?: $dir;
            if (isset($seenRealDirs[$realDir])) {
                continue;
            }
            $seenRealDirs[$realDir] = true;

            $iterator = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
            foreach ($iterator as $item) {
                $pathName = $item->getPathname();

                if ($item->isDir()) {
                    $queue[] = $pathName;
                    continue;
                }

                if (! $item->isFile()) {
                    continue;
                }

                if (strpos($pathName, $root) !== 0) {
                    continue;
                }

                $relative = ltrim(substr($pathName, strlen($root)), '/\\');
                $files[] = str_replace('\\', '/', $relative);
            }
        }

        return $files;
    }
}
