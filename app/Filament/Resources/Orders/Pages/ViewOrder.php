<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected string $view = 'filament.resources.orders.pages.view-order';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Alle Bestellungen')
                ->url(OrderResource::getUrl('index')),
            // Action::make('edit')
            //     ->label('Bearbeiten')
            //     ->url(OrderResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
}
