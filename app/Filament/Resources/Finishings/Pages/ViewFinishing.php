<?php

namespace App\Filament\Resources\Finishings\Pages;

use App\Filament\Resources\Finishings\FinishingResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewFinishing extends ViewRecord
{
    protected static string $resource = FinishingResource::class;

    protected string $view = 'filament.resources.finishings.pages.view-finishing';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Bearbeiten')
                ->url(fn (): string => FinishingResource::getUrl('edit', ['record' => $this->getRecord()])),
        ];
    }
}
