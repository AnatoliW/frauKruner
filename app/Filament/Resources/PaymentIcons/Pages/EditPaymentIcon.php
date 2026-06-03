<?php

namespace App\Filament\Resources\PaymentIcons\Pages;

use App\Filament\Resources\PaymentIcons\PaymentIconResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentIcon extends EditRecord
{
    protected static string $resource = PaymentIconResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
