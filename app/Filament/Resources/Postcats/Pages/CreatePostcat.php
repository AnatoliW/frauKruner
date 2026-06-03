<?php

namespace App\Filament\Resources\Postcats\Pages;

use App\Filament\Resources\Postcats\PostcatResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePostcat extends CreateRecord
{
    protected static string $resource = PostcatResource::class;
}
