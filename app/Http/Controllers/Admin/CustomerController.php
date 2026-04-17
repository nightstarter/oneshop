<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()
            ->when(! $request->boolean('with_inactive'), fn ($q) => $q->where('is_active', true))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->with('priceList')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($request->integer('per_page', 20));

        return response()->json($customers);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        return response()->json($customer->load('priceList'), JsonResponse::HTTP_CREATED);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer->load(['priceList', 'addresses', 'orders']));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer->update($request->validated());

        return response()->json($customer->fresh(['priceList', 'addresses']));
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
