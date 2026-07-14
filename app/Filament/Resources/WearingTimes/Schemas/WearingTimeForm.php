<?php

namespace App\Filament\Resources\WearingTimes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WearingTimeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->helperText('URL-Kurzname, z. B. 3-tage oder 1-woche.'),
                TextInput::make('days')
                    ->label('Tage')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->helperText('Anzahl der Tragetage für diese Option.'),
            ]);
    }
}
