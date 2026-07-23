<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grunddaten')
                    ->description('Name, Sortierung und Sichtbarkeit der Kategorie.')
                    ->schema([
                        TextInput::make('order')
                            ->label('Reihenfolge')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->helperText('Kleinere Zahl = weiter oben in der Liste.'),
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->helperText('URL-Kurzname, z. B. slips oder socken.'),
                        Select::make('parent_id')
                            ->label('Übergeordnete Kategorie')
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->orderBy('order')
                                    ->orderBy('name'),
                                ignoreRecord: true,
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Keine (Hauptkategorie)')
                            ->helperText('Leer lassen für eine Hauptkategorie.'),
                        Toggle::make('featured')
                            ->label('Auf der Startseite?')
                            ->default(false)
                            ->inline(false)
                            ->onColor('success')
                            ->helperText('Aktiv = Kategorie wird auf der Startseite angezeigt.')
                            ->afterStateHydrated(function (Toggle $component, $state): void {
                                $component->state((bool) ((int) $state));
                            })
                            ->dehydrateStateUsing(fn (bool $state): int => $state ? 1 : 0),
                    ])
                    ->columns(2),

                Section::make('Inhalt')
                    ->description('Texte für Startseite und Shop.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Überschrift')
                            ->maxLength(255)
                            ->helperText('Große Überschrift im Kategorie-Block.'),
                        Textarea::make('description')
                            ->label('Beschreibung')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Darstellung')
                    ->description('Farben und Bild für den Kategorie-Bereich.')
                    ->schema([
                        ColorPicker::make('color')
                            ->label('Hintergrundfarbe')
                            ->hex()
                            ->helperText('Hintergrundfarbe des Kategorie-Blocks auf der Startseite.'),
                        ColorPicker::make('font')
                            ->label('Schriftfarbe')
                            ->hex()
                            ->helperText('Textfarbe innerhalb des Kategorie-Blocks.'),
                        FileUpload::make('image')
                            ->label('Bild')
                            ->image()
                            ->disk('s3')
                            ->directory('categories')
                            ->columnSpanFull()
                            ->helperText('Kategoriebild für die Startseite.'),
                    ])
                    ->columns(2),
            ]);
    }
}
