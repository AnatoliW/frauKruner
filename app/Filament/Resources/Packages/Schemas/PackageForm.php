<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('price')
                    ->label('Preis')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->suffix(' €'),
                TextInput::make('days')
                    ->label('Laufzeit (Tage)')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->helperText('Anzahl der Tage, die der Push aktiv bleibt.'),
                Select::make('type')
                    ->label('Typ')
                    ->options([
                        'Product' => 'Produkt',
                        'Profile' => 'Profil',
                    ])
                    ->required()
                    ->native(false)
                    ->helperText('Produkt-Pakete gelten für Produkte, Profil-Pakete für Verkäuferprofile.'),
                Toggle::make('status')
                    ->label('Aktiv')
                    ->default(false)
                    ->inline(false)
                    ->onColor('success')
                    ->helperText('Nur aktive Push-Optionen sollten im Shop auswählbar sein.')
                    ->afterStateHydrated(function (Toggle $component, $state): void {
                        $component->state((bool) ((int) $state));
                    })
                    ->dehydrateStateUsing(fn (bool $state): int => $state ? 1 : 0),
            ]);
    }
}
