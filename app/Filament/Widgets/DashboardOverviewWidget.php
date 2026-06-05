<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-overview-widget';

    protected int | string | array $columnSpan = 'full';

    public function getOrdersCount(): int
    {
        return class_exists(\App\Order::class)
            ? \App\Order::children()
            ->paid()
            ->latest(\App\Order::CREATED_AT)
            ->count()
            : 0;
    }

    public function getProductsCount(): int
    {
        return class_exists(\App\Product::class)
            ? \App\Product::query()->count()
            : 0;
    }

    public function getOrdersUrl(): string
    {
        return url('/admin/orders');
    }

    public function getProductsUrl(): string
    {
        return url('/admin/products');
    }

    public function hasStorageSymlink(): bool
    {
        return is_link(public_path('storage')) || file_exists(public_path('storage'));
    }

    public function cleanupStorage(): void
    {
        clearstatcache();
    }
}