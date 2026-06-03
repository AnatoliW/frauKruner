<?php

namespace App\Filament\Resources\Additions\Pages;

use App\Filament\Resources\Additions\AdditionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAddition extends CreateRecord
{
    protected static string $resource = AdditionResource::class;
}
