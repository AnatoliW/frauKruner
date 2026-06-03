<?php

namespace App\Filament\Resources\Postcats\Pages;

use App\Filament\Resources\Postcats\PostcatResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostcat extends EditRecord
{
    protected static string $resource = PostcatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
