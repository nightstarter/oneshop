<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->integer('customer_id')))
            ->with(['customer', 'items'])
            ->orderByDesc('placed_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json($order->load(['customer', 'items', 'user']));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order->update($request->validated());

        return response()->json($order->fresh(['customer', 'items']));
    }
}
