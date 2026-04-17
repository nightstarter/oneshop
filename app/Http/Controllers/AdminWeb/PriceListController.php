<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePriceListRequest;
use App\Http\Requests\UpdatePriceListRequest;
use App\Models\PriceList;

class PriceListController extends Controller
{
    public function index()
    {
        $priceLists = PriceList::query()->orderBy('name')->paginate(20);

        return view('admin.price_lists.index', compact('priceLists'));
    }

    public function create()
    {
        return view('admin.price_lists.form', [
            'priceList' => new PriceList(),
            'isEdit' => false,
        ]);
    }

    public function store(StorePriceListRequest $request)
    {
        PriceList::create($request->validated());

        return redirect()->route('admin.price-lists.index')->with('success', __('messages.price_list_created'));
    }

    public function edit(PriceList $price_list)
    {
        return view('admin.price_lists.form', [
            'priceList' => $price_list,
            'isEdit' => true,
        ]);
    }

    public function update(UpdatePriceListRequest $request, PriceList $price_list)
    {
        $price_list->update($request->validated());

        return redirect()->route('admin.price-lists.index')->with('success', __('messages.price_list_updated'));
    }

    public function destroy(PriceList $price_list)
    {
        $price_list->delete();

        return redirect()->route('admin.price-lists.index')->with('success', __('messages.price_list_deleted'));
    }
}
