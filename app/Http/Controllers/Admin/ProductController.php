<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\ProductSearchInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\PriceCalculator;
use App\Services\ProductWriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly PriceCalculator $prices,
        private readonly ProductSearchInterface $search,
        private readonly ProductWriteService $productWriter,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if ($query !== '') {
            $products = $this->search->search(
                $query,
                $request->integer('per_page', 20),
                $request->integer('page', 1),
            );
        } else {
            $products = Product::query()
                ->when(! $request->boolean('with_inactive'), fn ($q) => $q->activeForSale())
                ->with('categories')
                ->orderBy('name')
                ->paginate($request->integer('per_page', 20));
        }

        return response()->json($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $product = $this->productWriter->create($data);

        if ($categoryIds) {
            $product->categories()->sync($categoryIds);
        }

        return response()->json($product->load('categories'), JsonResponse::HTTP_CREATED);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $product->load('categories');
        $customer = $request->user()?->customer;
        $price = $this->prices->calculate($product, $customer);

        return response()->json([
            'product' => $product,
            'price'   => $price,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids']);

        $product = $this->productWriter->update($product, $data);

        if (! is_null($categoryIds)) {
            $product->categories()->sync($categoryIds);
        }

        return response()->json($product->fresh('categories'));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
