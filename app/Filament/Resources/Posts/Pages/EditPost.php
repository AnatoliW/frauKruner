<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    public function getTitle(): string
    {
        return 'Beitrag bearbeiten';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Beitrag ansehen')
                ->icon('heroicon-m-eye')
                ->color('gray')
                ->url(fn (): string => route('post_details', ['slug' => $this->getRecord()->slug]))
                ->openUrlInNewTab(),
            DeleteAction::make()
                ->label('Löschen'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PostResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
