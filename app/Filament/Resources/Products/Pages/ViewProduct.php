<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.resources.products.pages.view-product';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Zurück zum Katalog')
                ->url(ProductResource::getUrl('index')),
            Action::make('boost')
                ->label('Produkt pushen')
                ->color('success')
                ->icon('heroicon-m-arrow-up')
                ->url(fn (): string => ProductResource::getUrl('boost', ['record' => $this->getRecord()])),
        ];
    }
}
