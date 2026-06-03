<?php

namespace App\Filament\Resources\Prodcats\Pages;

use App\Filament\Resources\Prodcats\ProdcatResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProdcat extends EditRecord
{
    protected static string $resource = ProdcatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
