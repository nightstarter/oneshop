<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\RendersThemeViews;
use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use RendersThemeViews;

    public function __construct(private readonly CartService $cart) {}

    public function index()
    {
        return $this->renderTheme('cart.index', [
            'items'  => $this->cart->items(),
            'totals' => $this->cart->totals(),
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity'   => ['integer', 'min:1'],
        ]);

        $this->cart->add(
            (int) $request->input('product_id'),
            (int) $request->input('quantity', 1),
        );

        return back()->with('success', __('messages.cart_product_added'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:0'],
        ]);

        $this->cart->update(
            (int) $request->input('product_id'),
            (int) $request->input('quantity'),
        );

        return back()->with('success', __('messages.cart_updated'));
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $this->cart->remove((int) $request->input('product_id'));

        return back()->with('success', __('messages.cart_product_removed'));
    }
}
