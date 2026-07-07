<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('email')
                    ->label('E-Mail-Adresse')
                    ->email(),
                TextInput::make('subject'),
                Textarea::make('message')
                    ->columnSpanFull(),
            ]);
    }
}
