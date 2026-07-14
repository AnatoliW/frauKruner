<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('question')
                    ->label('Frage')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('answer')
                    ->label('Antwort')
                    ->required()
                    ->columnSpanFull(),
                Select::make('type')
                    ->label('Typ')
                    ->options([
                        0 => 'Käufer/in',
                        1 => 'Verkäufer/in',
                    ])
                    ->required()
                    ->default(0)
                    ->native(false),
            ]);
    }
}
