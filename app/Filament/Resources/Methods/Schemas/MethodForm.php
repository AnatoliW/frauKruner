<?php

namespace App\Filament\Resources\Methods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('paypal'),
                TextInput::make('iban'),
                TextInput::make('bic'),
            ]);
    }
}
