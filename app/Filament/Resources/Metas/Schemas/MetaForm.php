<?php

namespace App\Filament\Resources\Metas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MetaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('metable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('metable_type')
                    ->required(),
                TextInput::make('column_name')
                    ->required(),
                Textarea::make('column_value')
                    ->columnSpanFull(),
            ]);
    }
}
