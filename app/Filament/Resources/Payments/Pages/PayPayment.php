<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class PayPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected string $view = 'filament.resources.payments.pages.pay-payment';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Zurueck')
                ->url(PaymentResource::getUrl('index')),
        ];
    }
}
