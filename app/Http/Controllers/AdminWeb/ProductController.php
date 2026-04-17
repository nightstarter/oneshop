<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()->with('categories')->orderBy('name')->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedCategories' => [],
            'isEdit' => false,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $product = Product::create($data);
        $product->categories()->sync($categoryIds);

        return redirect()->route('admin.products.index')->with('success', __('messages.product_created'));
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedCategories' => $product->categories()->pluck('categories.id')->all(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids']);

        $product->update($data);

        if ($categoryIds !== null) {
            $product->categories()->sync($categoryIds);
        }

        return redirect()->route('admin.products.index')->with('success', __('messages.product_updated'));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', __('messages.product_deleted'));
    }
}
