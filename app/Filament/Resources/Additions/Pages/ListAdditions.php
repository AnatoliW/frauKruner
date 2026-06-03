<?php

namespace App\Filament\Resources\Additions\Pages;

use App\Filament\Resources\Additions\AdditionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdditions extends ListRecords
{
    protected static string $resource = AdditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
