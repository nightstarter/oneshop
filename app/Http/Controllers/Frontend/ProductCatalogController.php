<?php

namespace App\Http\Controllers\Frontend;

use App\Contracts\ProductSearchInterface;
use App\Http\Controllers\Concerns\RendersThemeViews;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductCatalogController extends Controller
{
    use RendersThemeViews;

    public function __construct(
        private readonly PriceCalculator $prices,
        private readonly ProductSearchInterface $search,
    ) {}

    /** All products or search results. */
    public function index(Request $request)
    {
        $query = $request->input('q', '');

        if ($query !== '') {
            $products = $this->search->search(
                $query,
                $request->integer('per_page', 24),
                $request->integer('page', 1),
            );

            $products->getCollection()->loadMissing(['categories', 'productImages.mediaFile']);
        } else {
            $products = Product::query()
                ->where('is_active', true)
                ->with(['categories', 'productImages.mediaFile'])
                ->orderBy('name')
                ->paginate(24);
        }

        return $this->renderTheme('products.index', [
            'products'   => $products,
            'category'   => null,
            'searchQuery' => $query,
            'customer'   => Auth::user()?->customer,
        ]);
    }

    /** Products in a specific category. */
    public function category(Request $request, Category $category)
    {
        $products = $category->products()
            ->where('is_active', true)
            ->with(['categories', 'productImages.mediaFile'])
            ->orderBy('name')
            ->paginate(24);

        return $this->renderTheme('products.index', [
            'products'    => $products,
            'category'    => $category,
            'searchQuery' => '',
            'customer'    => Auth::user()?->customer,
        ]);
    }

    /** Product detail page. */
    public function show(Product $product)
    {
        abort_unless($product->isActiveForSale(), 404);

        $product->load(['categories', 'productImages.mediaFile']);
        $customer = Auth::user()?->customer;
        $price    = $this->prices->calculate($product, $customer);

        // For SEO products: compatibility data lives on the carrier.
        $carrier = $product->carrierProduct();
        if ($product->isSeoProduct() && ! $product->relationLoaded('parent')) {
            $product->load('parent');
        }
        $carrier->loadMissing(['deviceModels', 'partNumbers']);

        $related = Product::query()
            ->where('active', true)
            ->where('id', '!=', $product->id)
            ->whereNull('parent_product_id')   // show only carrier products as related
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $product->categories->pluck('id')))
            ->with(['categories', 'productImages.mediaFile'])
            ->take(4)
            ->get();

        return $this->renderTheme('products.show', compact(
            'product',
            'carrier',
            'price',
            'related',
            'customer',
        ));
    }
}
