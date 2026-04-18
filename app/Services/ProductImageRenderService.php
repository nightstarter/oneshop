<?php

namespace App\Services;

use App\Models\MediaFile;
use Illuminate\Support\Facades\Storage;

class ProductImageRenderService
{
    public function render(MediaFile $mediaFile, string $variant = 'main'): array
    {
        $disk = Storage::disk($mediaFile->disk);

        abort_unless($disk->exists($mediaFile->path), 404);
        abort_unless(extension_loaded('gd'), 500, 'GD extension is required for image watermark rendering.');

        $binary = $disk->get($mediaFile->path);
        $mimeType = $mediaFile->mime_type ?: 'application/octet-stream';

        if (!str_starts_with($mimeType, 'image/')) {
            abort(404);
        }

        $binary = $this->applyVariantResize($binary, $mimeType, $variant);
        $binary = $this->applyWatermark($binary, $mimeType);

        $lastModifiedTimestamp = $disk->lastModified($mediaFile->path);

        return [
            'content' => $binary,
            'mime_type' => $mimeType,
            'etag' => sha1($variant . '|' . $binary),
            'last_modified' => gmdate('D, d M Y H:i:s', $lastModifiedTimestamp) . ' GMT',
        ];
    }

    private function applyVariantResize(string $binary, string $mimeType, string $variant): string
    {
        $targetMax = match ($variant) {
            'thumb' => 240,
            'main' => 1280,
            default => 1280,
        };

        $source = $this->createImageFromBinary($binary, $mimeType);
        if ($source === false) {
            return $binary;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width <= $targetMax && $height <= $targetMax) {
            return $binary;
        }

        $ratio = min($targetMax / $width, $targetMax / $height);
        $newWidth = max(1, (int) floor($width * $ratio));
        $newHeight = max(1, (int) floor($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if ($resized === false) {
            imagedestroy($source);
            return $binary;
        }

        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $output = $this->encodeImageToBinary($resized, $mimeType);

        imagedestroy($source);
        imagedestroy($resized);

        return $output ?? $binary;
    }

    private function applyWatermark(string $binary, string $mimeType): string
    {
        $image = $this->createImageFromBinary($binary, $mimeType);
        if ($image === false) {
            return $binary;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $text = (string) config('shop.image_watermark_text', config('app.name', 'OneShop'));
        if ($text === '') {
            imagedestroy($image);
            return $binary;
        }

        $font = 4;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $padding = max(8, (int) floor(min($width, $height) * 0.02));

        $x = max(0, $width - $textWidth - $padding);
        $y = max(0, $height - $textHeight - $padding);

        $bgColor = imagecolorallocatealpha($image, 0, 0, 0, 70);
        imagefilledrectangle(
            $image,
            max(0, $x - 6),
            max(0, $y - 4),
            min($width, $x + $textWidth + 6),
            min($height, $y + $textHeight + 4),
            $bgColor
        );

        $textColor = imagecolorallocatealpha($image, 255, 255, 255, 45);
        imagestring($image, $font, $x, $y, $text, $textColor);

        $output = $this->encodeImageToBinary($image, $mimeType);
        imagedestroy($image);

        return $output ?? $binary;
    }

    private function createImageFromBinary(string $binary, string $mimeType)
    {
        return match ($mimeType) {
            'image/jpeg', 'image/jpg', 'image/png', 'image/webp' => @imagecreatefromstring($binary),
            default => false,
        };
    }

    private function encodeImageToBinary($image, string $mimeType): ?string
    {
        ob_start();

        $result = match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagejpeg($image, null, 90),
            'image/png' => imagepng($image, null, 6),
            'image/webp' => imagewebp($image, null, 90),
            default => false,
        };

        $content = ob_get_clean();

        if ($result === false || $content === false) {
            return null;
        }

        return $content;
    }
}
