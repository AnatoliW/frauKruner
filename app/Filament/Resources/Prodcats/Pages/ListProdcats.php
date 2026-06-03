<?php

namespace App\Filament\Resources\Prodcats\Pages;

use App\Filament\Resources\Prodcats\ProdcatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProdcats extends ListRecords
{
    protected static string $resource = ProdcatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
