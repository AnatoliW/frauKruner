<?php

namespace App\Filament\Resources\ProdcatProducts\Pages;

use App\Filament\Resources\ProdcatProducts\ProdcatProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProdcatProducts extends ListRecords
{
    protected static string $resource = ProdcatProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
