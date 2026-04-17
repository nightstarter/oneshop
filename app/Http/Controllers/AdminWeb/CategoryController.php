<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()->with('parent')->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form', [
            'category' => new Category(),
            'parents' => Category::query()->orderBy('name')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', __('messages.category_created'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents' => Category::query()->where('id', '!=', $category->id)->orderBy('name')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('success', __('messages.category_updated'));
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', __('messages.category_deleted'));
    }
}
