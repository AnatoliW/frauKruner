<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected string $view = 'filament.resources.categories.pages.view-category';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Bearbeiten')
                ->url(fn (): string => CategoryResource::getUrl('edit', ['record' => $this->getRecord()])),
        ];
    }
}
