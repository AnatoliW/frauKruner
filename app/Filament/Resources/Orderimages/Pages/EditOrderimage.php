<?php

namespace App\Filament\Resources\Orderimages\Pages;

use App\Filament\Resources\Orderimages\OrderimageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderimage extends EditRecord
{
    protected static string $resource = OrderimageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
