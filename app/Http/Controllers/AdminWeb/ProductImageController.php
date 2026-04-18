<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProductImageRequest;
use App\Http\Requests\Admin\UploadProductImagesRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductImageService;

class ProductImageController extends Controller
{
    public function __construct(private readonly ProductImageService $productImageService)
    {
    }

    public function store(UploadProductImagesRequest $request, Product $product)
    {
        $this->productImageService->upload($product, $request->file('images', []));

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', __('messages.product_images_uploaded'));
    }

    public function update(UpdateProductImageRequest $request, Product $product, ProductImage $productImage)
    {
        $this->ensureOwnership($product, $productImage);

        $this->productImageService->updateMeta(
            $productImage,
            $request->validated('alt'),
            (int) $request->validated('sort_order')
        );

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', __('messages.product_image_updated'));
    }

    public function makePrimary(Product $product, ProductImage $productImage)
    {
        $this->ensureOwnership($product, $productImage);

        $this->productImageService->setPrimary($product, $productImage);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', __('messages.product_image_main_set'));
    }

    public function destroy(Product $product, ProductImage $productImage)
    {
        $this->ensureOwnership($product, $productImage);

        $this->productImageService->delete($product, $productImage);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', __('messages.product_image_deleted'));
    }

    private function ensureOwnership(Product $product, ProductImage $productImage): void
    {
        abort_unless($productImage->product_id === $product->id, 404);
    }
}
