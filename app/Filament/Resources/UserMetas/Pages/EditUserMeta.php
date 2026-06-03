<?php

namespace App\Filament\Resources\UserMetas\Pages;

use App\Filament\Resources\UserMetas\UserMetaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserMeta extends EditRecord
{
    protected static string $resource = UserMetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
