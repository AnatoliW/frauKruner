<?php

namespace App\Filament\Resources\Notifications\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title'),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image(),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('product_id')
                    ->numeric(),
                TextInput::make('role')
                    ->numeric(),
                TextInput::make('seen')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('is_email')
                    ->email()
                    ->numeric(),
                TextInput::make('verified')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
