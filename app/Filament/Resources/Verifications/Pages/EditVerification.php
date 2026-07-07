<?php

namespace App\Filament\Resources\Verifications\Pages;

use App\Filament\Resources\Verifications\VerificationResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVerification extends EditRecord
{
    protected static string $resource = VerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('Detailansicht')
                ->icon('heroicon-m-eye')
                ->color('gray')
                ->url(fn (): string => VerificationResource::getUrl('edit', ['record' => $this->getRecord()])),
            DeleteAction::make()
                ->label('Löschen'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return VerificationResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
