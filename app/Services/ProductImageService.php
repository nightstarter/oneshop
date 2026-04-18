<?php

namespace App\Services;

use App\Models\MediaFile;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageService
{
    public function upload(Product $product, array $uploadedFiles): void
    {
        DB::transaction(function () use ($product, $uploadedFiles): void {
            $nextOrder = (int) ($product->productImages()->max('sort_order') ?? 0);
            $hasPrimary = $product->productImages()->where('is_primary', true)->exists();

            foreach ($uploadedFiles as $uploadedFile) {
                if (!$uploadedFile instanceof UploadedFile) {
                    continue;
                }

                $nextOrder += 10;
                $mediaFile = $this->storeOrReuseMediaFile($uploadedFile);

                if ($product->productImages()->where('media_file_id', $mediaFile->id)->exists()) {
                    continue;
                }

                $productImage = $product->productImages()->create([
                    'media_file_id' => $mediaFile->id,
                    'sort_order' => $nextOrder,
                    'alt' => $product->name,
                    'is_primary' => !$hasPrimary,
                ]);

                if ($productImage->is_primary) {
                    $hasPrimary = true;
                }
            }
        });
    }

    public function updateMeta(ProductImage $productImage, ?string $alt, int $sortOrder): void
    {
        $productImage->update([
            'alt' => $alt,
            'sort_order' => $sortOrder,
        ]);
    }

    public function setPrimary(Product $product, ProductImage $productImage): void
    {
        DB::transaction(function () use ($product, $productImage): void {
            $product->productImages()->update(['is_primary' => false]);
            $productImage->update(['is_primary' => true]);
        });
    }

    public function delete(Product $product, ProductImage $productImage): void
    {
        DB::transaction(function () use ($product, $productImage): void {
            $wasPrimary = $productImage->is_primary;
            $mediaFile = $productImage->mediaFile;

            $productImage->delete();

            if ($wasPrimary) {
                $fallback = $product->productImages()->orderBy('sort_order')->first();
                if ($fallback !== null) {
                    $fallback->update(['is_primary' => true]);
                }
            }

            if ($mediaFile !== null && !$mediaFile->productImages()->exists()) {
                Storage::disk($mediaFile->disk)->delete($mediaFile->path);
                $mediaFile->delete();
            }
        });
    }

    private function storeOrReuseMediaFile(UploadedFile $uploadedFile): MediaFile
    {
        $checksum = hash_file('sha256', $uploadedFile->getRealPath());

        $existing = MediaFile::query()->where('checksum', $checksum)->first();
        if ($existing !== null) {
            return $existing;
        }

        $storedPath = $this->storeWithOriginalFilename($uploadedFile);

        [$width, $height] = $this->detectDimensions($uploadedFile);

        return MediaFile::query()->create([
            'disk' => 'private_products',
            'path' => $storedPath,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType() ?: 'application/octet-stream',
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'size' => $uploadedFile->getSize() ?: 0,
            'checksum' => $checksum,
            'width' => $width,
            'height' => $height,
        ]);
    }

    private function storeWithOriginalFilename(UploadedFile $uploadedFile): string
    {
        $disk = Storage::disk('private_products');
        $directory = date('Y/m');

        $originalName = $uploadedFile->getClientOriginalName();
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        // Keep original filename semantics while neutralizing path separators.
        $safeBaseName = trim(Str::of($baseName)->replace(['\\', '/'], '-')->value());

        if ($safeBaseName === '') {
            $safeBaseName = 'image';
        }

        $candidateName = $extension !== '' ? $safeBaseName . '.' . $extension : $safeBaseName;
        $storedPath = $directory . '/' . $candidateName;
        $suffix = 1;

        while ($disk->exists($storedPath)) {
            $candidateName = $extension !== ''
                ? $safeBaseName . '-' . $suffix . '.' . $extension
                : $safeBaseName . '-' . $suffix;

            $storedPath = $directory . '/' . $candidateName;
            $suffix++;
        }

        $disk->putFileAs($directory, $uploadedFile, $candidateName);

        return $storedPath;
    }

    private function detectDimensions(UploadedFile $uploadedFile): array
    {
        $size = @getimagesize($uploadedFile->getRealPath());

        if (!is_array($size)) {
            return [null, null];
        }

        return [$size[0] ?? null, $size[1] ?? null];
    }
}
