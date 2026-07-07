<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.pages.view-user';

    public function getTitle(): string
    {
        $user = $this->getRecord();
        $label = trim(($user->username ?: trim(($user->name ?? '') . ' ' . ($user->last_name ?? ''))));

        return $label !== '' ? "Nutzer: {$label}" : 'Nutzer';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Zurück zur Übersicht')
                ->url(UserResource::getUrl('index')),
            Action::make('boost')
                ->label('Profil pushen')
                ->color('success')
                ->icon('heroicon-m-arrow-up')
                ->visible(fn (): bool => (int) $this->getRecord()->role_id === 3)
                ->url(fn (): string => UserResource::getUrl('boost', ['record' => $this->getRecord()])),
            Action::make('edit')
                ->label('Bearbeiten')
                ->icon('heroicon-m-pencil-square')
                ->url(fn (): string => UserResource::getUrl('edit-form', ['record' => $this->getRecord()])),
        ];
    }
}
