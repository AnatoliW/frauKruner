<?php

namespace App\Filament\Resources\Orderimages\Pages;

use App\Filament\Resources\Orderimages\OrderimageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderimages extends ListRecords
{
    protected static string $resource = OrderimageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
