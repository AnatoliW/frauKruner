<?php

namespace App\Filament\Resources\Ratings\Pages;

use App\Filament\Resources\Ratings\RatingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRating extends CreateRecord
{
    protected static string $resource = RatingResource::class;

    public function getTitle(): string
    {
        return 'Bewertung erstellen';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 0;
        $data['product_id'] = null;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return RatingResource::getUrl('index');
    }
}
