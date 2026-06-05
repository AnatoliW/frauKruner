<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.pages.view-user';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Bearbeiten')
                ->url(fn (): string => UserResource::getUrl('edit', ['record' => $this->getRecord()])),
        ];
    }
}
