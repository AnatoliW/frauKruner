<?php

namespace App\Filament\Resources\Boosts\Pages;

use App\Filament\Resources\Boosts\BoostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBoosts extends ListRecords
{
    protected static string $resource = BoostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
