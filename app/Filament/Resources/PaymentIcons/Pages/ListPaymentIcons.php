<?php

namespace App\Filament\Resources\PaymentIcons\Pages;

use App\Filament\Resources\PaymentIcons\PaymentIconResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentIcons extends ListRecords
{
    protected static string $resource = PaymentIconResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
