<?php

namespace App\Filament\Resources\Boosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BoostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('boostable_id')
                    ->numeric(),
                TextInput::make('boostable_type'),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('package_id')
                    ->required()
                    ->numeric(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('start_day'),
                DateTimePicker::make('end_day'),
                TextInput::make('tax')
                    ->numeric(),
                TextInput::make('user_info'),
                TextInput::make('base_price')
                    ->numeric()
                    ->prefix('$'),
            ]);
    }
}
