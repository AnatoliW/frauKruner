<?php

namespace App\Filament\Resources\WearingTimes\Pages;

use App\Filament\Resources\WearingTimes\WearingTimeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWearingTime extends CreateRecord
{
    protected static string $resource = WearingTimeResource::class;

    public function getTitle(): string
    {
        return 'Tragedauer erstellen';
    }
}
