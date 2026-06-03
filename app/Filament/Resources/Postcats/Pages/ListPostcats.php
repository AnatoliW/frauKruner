<?php

namespace App\Filament\Resources\Postcats\Pages;

use App\Filament\Resources\Postcats\PostcatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostcats extends ListRecords
{
    protected static string $resource = PostcatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
