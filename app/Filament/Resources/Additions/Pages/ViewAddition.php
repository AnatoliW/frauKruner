<?php

namespace App\Filament\Resources\Additions\Pages;

use App\Filament\Resources\Additions\AdditionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewAddition extends ViewRecord
{
    protected static string $resource = AdditionResource::class;

    protected string $view = 'filament.resources.additions.pages.view-addition';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Bearbeiten')
                ->url(fn (): string => AdditionResource::getUrl('edit', ['record' => $this->getRecord()])),
        ];
    }
}
