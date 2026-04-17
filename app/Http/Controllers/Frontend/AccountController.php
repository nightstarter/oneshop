<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\RendersThemeViews;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    use RendersThemeViews;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user     = Auth::user();
        $customer = $user->customer;

        return $this->renderTheme('account.index', compact('user', 'customer'));
    }

    public function orders()
    {
        $user = Auth::user();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->orderByDesc('placed_at')
            ->paginate(10);

        return $this->renderTheme('account.orders', compact('orders'));
    }

    public function showOrder(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load('items');

        return $this->renderTheme('account.order', compact('order'));
    }
}
