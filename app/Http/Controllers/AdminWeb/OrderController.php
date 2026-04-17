<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()->with('customer')->orderByDesc('placed_at')->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items', 'customer', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $order->update($request->validated());

        return redirect()->route('admin.orders.show', $order)->with('success', __('messages.order_status_updated'));
    }
}
