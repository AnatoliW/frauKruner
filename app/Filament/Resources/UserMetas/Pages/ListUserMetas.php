<?php

namespace App\Filament\Resources\UserMetas\Pages;

use App\Filament\Resources\UserMetas\UserMetaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserMetas extends ListRecords
{
    protected static string $resource = UserMetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
