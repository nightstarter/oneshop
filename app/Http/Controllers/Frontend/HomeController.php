<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\RendersThemeViews;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    use RendersThemeViews;

    public function index()
    {
        $featuredProducts = Product::query()
            ->where('is_active', true)
            ->with(['categories', 'productImages.mediaFile'])
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(6)
            ->get();

        return $this->renderTheme('home', compact('featuredProducts', 'categories'));
    }
}
