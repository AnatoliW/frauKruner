<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required(),
                TextInput::make('display_name')
                    ->required(),
                Textarea::make('value')
                    ->columnSpanFull(),
                Textarea::make('details')
                    ->columnSpanFull(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('group'),
            ]);
    }
}
