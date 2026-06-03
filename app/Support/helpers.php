<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if (! function_exists('media_url')) {
    function media_url(?string $path, string $fallback = 'assets/img/user.png'): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return asset($fallback);
        }

        if (Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return $path;
        }

        if (Str::startsWith($path, ['assets/', 'images/', 'storage/'])) {
            return asset($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        $value = \App\Models\Setting::query()->where('key', $key)->value('value');

        return $value ?? $default;
    }
}

if (! function_exists('menu')) {
    function menu(string $name, string $format = 'collection'): mixed
    {
        try {
            $menuId = DB::table('menus')->where('name', $name)->value('id');

            if (! $menuId) {
                return $format === '_json' ? collect() : collect();
            }

            $items = DB::table('menu_items')
                ->where('menu_id', $menuId)
                ->whereNull('parent_id')
                ->orderBy('order')
                ->get(['title', 'url', 'target']);

            return $format === '_json' ? $items : $items;
        } catch (\Throwable $throwable) {
            return $format === '_json' ? collect() : collect();
        }
    }
}