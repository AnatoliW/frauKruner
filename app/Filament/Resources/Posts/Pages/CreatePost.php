<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    public function getTitle(): string
    {
        return 'Beitrag erstellen';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_id'] = auth()->id();
        $data['featured'] = 0;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return PostResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
