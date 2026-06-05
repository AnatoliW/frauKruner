<?php

namespace App\Filament\Resources\Boosts\Pages;

use App\Filament\Resources\Boosts\BoostResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewBoost extends ViewRecord
{
    protected static string $resource = BoostResource::class;

    protected string $view = 'filament.resources.boosts.pages.view-boost-invoice';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Alle Boosts')
                ->url(BoostResource::getUrl('index')),
            // Action::make('edit')
            //     ->label('Bearbeiten')
            //     ->url(BoostResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
}
