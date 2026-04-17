<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->when($request->boolean('with_inactive'), fn ($q) => $q, fn ($q) => $q->where('is_active', true))
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return response()->json($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json($category->load('parent'), JsonResponse::HTTP_CREATED);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json($category->load(['parent', 'children']));
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json($category->fresh(['parent', 'children']));
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
