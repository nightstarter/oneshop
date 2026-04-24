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

        $stamp = $this->createWatermarkStamp($text, $width, $height);
        if ($stamp === false) {
            imagedestroy($image);
            return $binary;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $stampWidth = imagesx($stamp);
        $stampHeight = imagesy($stamp);
        $stepX = max(1, (int) floor($stampWidth * 0.78));
        $stepY = max(1, (int) floor($stampHeight * 0.9));

        for ($y = -$stampHeight; $y < $height + $stampHeight; $y += $stepY) {
            $rowOffset = (((int) floor($y / $stepY)) % 2 === 0)
                ? (int) floor($stampWidth * 0.18)
                : (int) floor(-$stampWidth * 0.22);

            for ($x = -$stampWidth + $rowOffset; $x < $width + $stampWidth; $x += $stepX) {
                imagecopy($image, $stamp, $x, $y, 0, 0, $stampWidth, $stampHeight);
            }
        }

        imagedestroy($stamp);

        $output = $this->encodeImageToBinary($image, $mimeType);
        imagedestroy($image);

        return $output ?? $binary;
    }

    private function createWatermarkStamp(string $text, int $imageWidth, int $imageHeight)
    {
        $font = max(3, min(5, (int) floor(min($imageWidth, $imageHeight) / 220)));
        $textWidth = max(1, imagefontwidth($font) * strlen($text));
        $textHeight = imagefontheight($font);
        $paddingX = max(18, (int) floor($textWidth * 0.16));
        $paddingY = max(12, (int) floor($textHeight * 0.9));

        $canvasWidth = $textWidth + ($paddingX * 2);
        $canvasHeight = $textHeight + ($paddingY * 2);

        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
        if ($canvas === false) {
            return false;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $canvasWidth, $canvasHeight, $transparent);

        $shadowColor = imagecolorallocatealpha($canvas, 0, 0, 0, 108);
        $textColor = imagecolorallocatealpha($canvas, 255, 255, 255, 84);

        imagestring($canvas, $font, $paddingX + 1, $paddingY + 1, $text, $shadowColor);
        imagestring($canvas, $font, $paddingX, $paddingY, $text, $textColor);

        $rotated = imagerotate($canvas, 32, $transparent);
        imagedestroy($canvas);

        if ($rotated === false) {
            return false;
        }

        imagealphablending($rotated, true);
        imagesavealpha($rotated, true);

        return $rotated;
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
