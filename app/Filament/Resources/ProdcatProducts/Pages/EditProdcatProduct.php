<?php

namespace App\Filament\Resources\ProdcatProducts\Pages;

use App\Filament\Resources\ProdcatProducts\ProdcatProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProdcatProduct extends EditRecord
{
    protected static string $resource = ProdcatProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
