<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Filament\Resources\Notifications\NotificationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNotification extends EditRecord
{
    protected static string $resource = NotificationResource::class;

    public function getTitle(): string
    {
        return 'Nachricht bearbeiten';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['image'] = filled($data['image'] ?? null) ? $data['image'] : 'notifications/icon.png';

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Löschen'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return NotificationResource::getUrl('index');
    }
}
