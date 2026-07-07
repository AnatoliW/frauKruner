<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('Detailansicht')
                ->icon('heroicon-m-eye')
                ->color('gray')
                ->url(fn (): string => UserResource::getUrl('edit', ['record' => $this->getRecord()])),
            DeleteAction::make()
                ->label('Löschen'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
