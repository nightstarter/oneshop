<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::query()
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.form', [
            'page'   => new Page(),
            'isEdit' => false,
        ]);
    }

    public function store(StorePageRequest $request)
    {
        $data = $request->validated();

        if (! empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        Page::create($data);

        return redirect()->route('admin.pages.index')
            ->with('success', __('messages.page_created'));
    }

    public function edit(Page $page)
    {
        return view('admin.pages.form', [
            'page'   => $page,
            'isEdit' => true,
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page)
    {
        $data = $request->validated();

        // Set published_at when first publishing
        if (! empty($data['is_published']) && ! $page->published_at) {
            $data['published_at'] = now();
        }

        // Clear published_at when un-publishing
        if (empty($data['is_published'])) {
            $data['published_at'] = null;
        }

        $page->update($data);

        return redirect()->route('admin.pages.index')
            ->with('success', __('messages.page_updated'));
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', __('messages.page_deleted'));
    }
}
