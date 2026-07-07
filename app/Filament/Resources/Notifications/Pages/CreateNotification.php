<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Filament\Resources\Notifications\NotificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;

    public function getTitle(): string
    {
        return 'Nachricht erstellen';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['seen'] = 0;
        $data['verified'] = (int) ($data['verified'] ?? 0);
        $data['image'] = filled($data['image'] ?? null) ? $data['image'] : 'notifications/icon.png';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return NotificationResource::getUrl('index');
    }
}
