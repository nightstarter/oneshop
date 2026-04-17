<?php

namespace App\Support;

use App\Models\ShopSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ShopSettings
{
    private const CACHE_PREFIX = 'shop_setting:';

    public function get(string $key, mixed $default = null): mixed
    {
        if (! $this->tableExists()) {
            return $default;
        }

        return Cache::rememberForever(self::CACHE_PREFIX . $key, function () use ($key, $default) {
            return ShopSetting::query()
                ->where('key', $key)
                ->value('value') ?? $default;
        });
    }

    public function set(string $key, mixed $value): void
    {
        if (! $this->tableExists()) {
            return;
        }

        ShopSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value],
        );

        Cache::forget(self::CACHE_PREFIX . $key);
    }

    public function activeTheme(): string
    {
        $defaultTheme = (string) config('shop.active_theme', 'default');
        $availableThemes = $this->availableThemes();
        $savedTheme = (string) $this->get('active_theme', $defaultTheme);

        if (in_array($savedTheme, $availableThemes, true)) {
            return $savedTheme;
        }

        return in_array($defaultTheme, $availableThemes, true) ? $defaultTheme : 'default';
    }

    public function availableThemes(): array
    {
        $themesPath = resource_path('themes');

        if (! File::isDirectory($themesPath)) {
            return ['default'];
        }

        $themes = collect(File::directories($themesPath))
            ->map(static fn (string $directory) => basename($directory))
            ->filter()
            ->sort()
            ->values()
            ->all();

        if (! in_array('default', $themes, true)) {
            array_unshift($themes, 'default');
        }

        return array_values(array_unique($themes));
    }

    private function tableExists(): bool
    {
        static $exists;

        if ($exists !== null) {
            return $exists;
        }

        try {
            $exists = Schema::hasTable('shop_settings');
        } catch (Throwable) {
            $exists = false;
        }

        return $exists;
    }
}