<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\PriceList;
use App\Models\User;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::query()->with('priceList')->orderBy('last_name')->orderBy('first_name')->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.form', [
            'customer' => new Customer(),
            'users' => User::query()->orderBy('name')->get(),
            'priceLists' => PriceList::query()->where('is_active', true)->orderBy('name')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());

        return redirect()->route('admin.customers.index')->with('success', __('messages.customer_created'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.form', [
            'customer' => $customer,
            'users' => User::query()->orderBy('name')->get(),
            'priceLists' => PriceList::query()->where('is_active', true)->orderBy('name')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('admin.customers.index')->with('success', __('messages.customer_updated'));
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', __('messages.customer_deleted'));
    }
}
