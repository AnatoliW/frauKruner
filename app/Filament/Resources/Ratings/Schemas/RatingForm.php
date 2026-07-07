<?php

namespace App\Filament\Resources\Ratings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RatingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('status')
                    ->numeric()
                    ->default(0),
                TextInput::make('product_id')
                    ->numeric(),
                TextInput::make('name'),
                TextInput::make('email')
                    ->label('E-Mail-Adresse')
                    ->email(),
                TextInput::make('rating'),
                Textarea::make('review')
                    ->columnSpanFull(),
                TextInput::make('vendor_id')
                    ->numeric(),
            ]);
    }
}
