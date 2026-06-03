<?php

namespace App\Filament\Resources\Additions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdditionForm
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
