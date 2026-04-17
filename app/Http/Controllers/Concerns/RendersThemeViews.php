<?php

namespace App\Http\Controllers\Concerns;

use App\Support\ThemeView;

trait RendersThemeViews
{
    protected function renderTheme(string $view, array $data = [])
    {
        return view(app(ThemeView::class)->resolve($view), $data);
    }
}