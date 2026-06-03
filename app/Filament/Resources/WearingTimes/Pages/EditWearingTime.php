<?php

namespace App\Filament\Resources\WearingTimes\Pages;

use App\Filament\Resources\WearingTimes\WearingTimeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWearingTime extends EditRecord
{
    protected static string $resource = WearingTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
