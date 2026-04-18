<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use App\Services\ProductImageRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductImageViewController extends Controller
{
    public function __construct(private readonly ProductImageRenderService $renderService)
    {
    }

        public function placeholder(Request $request)
        {
                $variant = (string) $request->query('variant', 'main');

                $size = match ($variant) {
                        'thumb' => [240, 240],
                        default => [1280, 960],
                };

                [$width, $height] = $size;
                $innerWidth = max(1, $width - 32);
                $innerHeight = max(1, $height - 32);
                $label = e((string) __('shop.image_placeholder_text'));

                $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#f2f4f7"/>
            <stop offset="100%" stop-color="#dfe3e8"/>
        </linearGradient>
    </defs>
    <rect width="100%" height="100%" fill="url(#bg)" />
    <g fill="none" stroke="#b4bcc5" stroke-width="3">
        <rect x="16" y="16" width="{$innerWidth}" height="{$innerHeight}" rx="14" />
    </g>
    <g fill="#8b95a1" font-family="Arial, sans-serif" text-anchor="middle">
        <text x="50%" y="48%" font-size="34">{$label}</text>
    </g>
</svg>
SVG;

                return response($svg, 200, [
                        'Content-Type' => 'image/svg+xml; charset=UTF-8',
                        'Cache-Control' => 'public, max-age=3600',
                        'X-Content-Type-Options' => 'nosniff',
                ]);
        }

    public function show(Request $request, MediaFile $mediaFile)
    {
        abort_unless($this->canView($mediaFile), 404);

        $variant = (string) $request->query('variant', 'main');
        $payload = $this->renderService->render($mediaFile, $variant);

        $incomingEtag = trim((string) $request->header('If-None-Match'), '"');
        if ($incomingEtag !== '' && hash_equals($payload['etag'], $incomingEtag)) {
            return response('', 304, [
                'ETag' => '"' . $payload['etag'] . '"',
                'Cache-Control' => 'public, max-age=300',
            ]);
        }

        return response($payload['content'], 200, [
            'Content-Type' => $payload['mime_type'],
            'Content-Disposition' => 'inline; filename="product-image-' . $mediaFile->id . '"',
            'Cache-Control' => 'public, max-age=300',
            'ETag' => '"' . $payload['etag'] . '"',
            'Last-Modified' => $payload['last_modified'],
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function canView(MediaFile $mediaFile): bool
    {
        $isPublic = $mediaFile->products()->where('is_active', true)->exists();
        if ($isPublic) {
            return true;
        }

        $user = Auth::user();

        return (bool) ($user?->is_admin ?? false);
    }
}
