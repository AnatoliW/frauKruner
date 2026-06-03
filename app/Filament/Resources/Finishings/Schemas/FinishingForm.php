<?php

namespace App\Filament\Resources\Finishings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FinishingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('slug'),
            ]);
    }
}
