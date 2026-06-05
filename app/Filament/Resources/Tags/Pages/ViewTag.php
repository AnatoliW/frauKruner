<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewTag extends ViewRecord
{
    protected static string $resource = TagResource::class;

    protected string $view = 'filament.resources.tags.pages.view-tag';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Bearbeiten')
                ->url(fn (): string => TagResource::getUrl('edit', ['record' => $this->getRecord()])),
        ];
    }
}
