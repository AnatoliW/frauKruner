<?php

namespace App\Filament\Resources\Postcats\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;

class PostcatForm
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
                    ->maxLength(255),
                Select::make('parent_id')
                    ->label('Übergeordnete Kategorie')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('name'),
                        ignoreRecord: true,
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->placeholder('Keine (Hauptkategorie)')
                    ->helperText('Leer lassen für eine Hauptkategorie.'),
            ]);
    }
}
