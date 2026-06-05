<?php

namespace App\Filament\Resources\Prepayments\Pages;

use App\Filament\Resources\Prepayments\PrepaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListPrepayments extends ListRecords
{
    protected static string $resource = PrepaymentResource::class;

    protected static ?string $title = 'Bestellungen(Vorkasse)';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
