<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Support\ShopSettings;

class DashboardController extends Controller
{
    public function index(ShopSettings $settings)
    {
        return view('admin.dashboard', [
            'productsCount' => Product::count(),
            'categoriesCount' => Category::count(),
            'customersCount' => Customer::count(),
            'ordersCount' => Order::count(),
            'latestOrders' => Order::query()->latest('placed_at')->take(8)->get(),
            'activeTheme' => $settings->activeTheme(),
            'availableThemesCount' => count($settings->availableThemes()),
        ]);
    }
}
