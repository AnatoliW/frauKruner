<?php

namespace App\Filament\Resources\Points\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PointForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('pointable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('pointable_type')
                    ->required(),
                TextInput::make('points')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
