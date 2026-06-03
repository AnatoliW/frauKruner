<?php

namespace App\Filament\Resources\WearingTimes\Pages;

use App\Filament\Resources\WearingTimes\WearingTimeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWearingTimes extends ListRecords
{
    protected static string $resource = WearingTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
