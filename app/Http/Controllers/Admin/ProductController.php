<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\ProductSearchInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\PriceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly PriceCalculator $prices,
        private readonly ProductSearchInterface $search,
    ) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->filled('q')) {
            $products = $this->search->search(
                $request->input('q'),
                $request->integer('per_page', 20),
                $request->integer('page', 1),
            );
        } else {
            $products = Product::query()
                ->when(! $request->boolean('with_inactive'), fn ($q) => $q->where('is_active', true))
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

        $product = Product::create($data);

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

        $product->update($data);

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
