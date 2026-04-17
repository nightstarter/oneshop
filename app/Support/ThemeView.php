<?php

namespace App\Support;

use Illuminate\Contracts\View\Factory as ViewFactory;
use InvalidArgumentException;

class ThemeView
{
    private const DEFAULT_THEME = 'default';

    public function __construct(
        private readonly ViewFactory $views,
    ) {
    }

    public function theme(): string
    {
        return (string) config('shop.active_theme', self::DEFAULT_THEME);
    }

    public function defaultTheme(): string
    {
        return self::DEFAULT_THEME;
    }

    public function resolve(string $view): string
    {
        $themedView = 'theme::' . ltrim($view, '.');

        if ($this->views->exists($themedView)) {
            return $themedView;
        }

        $defaultThemeView = 'theme-default::' . ltrim($view, '.');

        if ($this->theme() !== self::DEFAULT_THEME && $this->views->exists($defaultThemeView)) {
            return $defaultThemeView;
        }

        if ($this->views->exists($view)) {
            return $view;
        }

        throw new InvalidArgumentException(sprintf(
            'View [%s] was not found in active theme [%s] or default view paths.',
            $view,
            $this->theme(),
        ));
    }
}
