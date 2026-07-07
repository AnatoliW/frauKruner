<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    public function getTitle(): string
    {
        return 'Seite bearbeiten';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Seite ansehen')
                ->icon('heroicon-m-eye')
                ->color('gray')
                ->url(fn (): string => route('page', ['slug' => $this->getRecord()->slug]))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->getRecord()->status === 'ACTIVE'),
            DeleteAction::make()
                ->label('Löschen'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PageResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
