<?php

namespace App\Filament\Resources\Logs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('admin_id')
                    ->numeric(),
                TextInput::make('name'),
                TextInput::make('email')
                    ->label('E-Mail-Adresse')
                    ->email(),
                Textarea::make('details')
                    ->columnSpanFull(),
            ]);
    }
}
