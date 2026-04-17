<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePriceListRequest;
use App\Http\Requests\UpdatePriceListRequest;
use App\Models\PriceList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceListController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $priceLists = PriceList::query()
            ->when(! $request->boolean('with_inactive'), fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return response()->json($priceLists);
    }

    public function store(StorePriceListRequest $request): JsonResponse
    {
        $priceList = PriceList::create($request->validated());

        return response()->json($priceList, JsonResponse::HTTP_CREATED);
    }

    public function show(PriceList $priceList): JsonResponse
    {
        return response()->json($priceList->load('products'));
    }

    public function update(UpdatePriceListRequest $request, PriceList $priceList): JsonResponse
    {
        $priceList->update($request->validated());

        return response()->json($priceList->fresh('products'));
    }

    public function destroy(PriceList $priceList): JsonResponse
    {
        $priceList->delete();

        return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
