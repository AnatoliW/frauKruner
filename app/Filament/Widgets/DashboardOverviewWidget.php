<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-overview-widget';

    protected int | string | array $columnSpan = 'full';

    /**
     * Zahl der bezahlten Bestellungen – nicht der Positionen.
     *
     * Muss zu OrderResource::getEloquentQuery() passen, denn die Kachel verlinkt
     * genau diese Liste. Vorher wurden hier Positionen gezählt: Eine Bestellung
     * mit zwei Artikeln zählte doppelt, und die Zahl stimmte nicht mit den
     * Zeilen der Liste überein.
     */
    public function getOrdersCount(): int
    {
        return class_exists(\App\Order::class)
            ? \App\Order::query()
                ->whereNull('parent_id')
                ->paid()
                ->count()
            : 0;
    }

    public function getProductsCount(): int
    {
        return class_exists(\App\Product::class)
            ? \App\Product::query()
                ->whereNull('parent_id')
                ->where('status', 1)
                ->count()
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