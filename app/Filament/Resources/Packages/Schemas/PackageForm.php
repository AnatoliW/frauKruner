<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('days')
                    ->numeric(),
                TextInput::make('status')
                    ->numeric()
                    ->default(0),
                Textarea::make('type')
                    ->columnSpanFull(),
            ]);
    }
}
