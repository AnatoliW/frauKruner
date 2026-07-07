<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Gutscheindaten')
                    ->description('Code und Rabattbetrag für den Warenkorb.')
                    ->schema([
                        TextInput::make('code')
                            ->label('Gutscheincode')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Der Code, den Käufer/innen im Warenkorb eingeben.')
                            ->columnSpanFull(),
                        TextInput::make('discount')
                            ->label('Rabattbetrag')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->suffix(' €')
                            ->helperText('Wird als fester Betrag vom Warenkorb abgezogen.'),
                    ])
                    ->columns(2),

                Section::make('Gültigkeit & Bedingungen')
                    ->description('Ablaufdatum, Einlöselimit und Mindestbestellwert.')
                    ->schema([
                        DatePicker::make('expire_at')
                            ->label('Gültig bis')
                            ->required()
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->helperText('Nach diesem Datum kann der Gutschein nicht mehr eingelöst werden.'),
                        TextInput::make('limit')
                            ->label('Maximale Einlösungen')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1)
                            ->helperText('Wie oft der Gutschein insgesamt verwendet werden darf.'),
                        TextInput::make('minimum_cart')
                            ->label('Mindestbestellwert')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' €')
                            ->default(0)
                            ->helperText('Warenkorbwert, der mindestens erreicht sein muss.'),
                    ])
                    ->columns(2),

                Section::make('Nutzung')
                    ->description('Statistik zur bisherigen Einlösung.')
                    ->schema([
                        TextInput::make('used')
                            ->label('Bereits eingelöst')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Wird beim Einlösen im Shop automatisch erhöht. Nur bei Bedarf manuell anpassen.'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
