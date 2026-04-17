<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Support\ShopSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ThemeController extends Controller
{
    public function index(ShopSettings $settings): View
    {
        return view('admin.themes.index', [
            'activeTheme' => $settings->activeTheme(),
            'themes' => $settings->availableThemes(),
        ]);
    }

    public function update(Request $request, ShopSettings $settings): RedirectResponse
    {
        $themes = $settings->availableThemes();

        $data = $request->validate([
            'active_theme' => ['required', 'string', Rule::in($themes)],
        ]);

        $settings->set('active_theme', $data['active_theme']);

        return redirect()
            ->route('admin.themes.index')
            ->with('success', __('messages.theme_saved', ['theme' => $data['active_theme']]));
    }
}