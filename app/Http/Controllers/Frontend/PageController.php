<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RendersThemeViews;
use App\Models\Page;

class PageController extends Controller
{
    use RendersThemeViews;

    public function show(string $slug)
    {
        $page = Page::published()
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->renderTheme('pages.show', compact('page'));
    }
}
