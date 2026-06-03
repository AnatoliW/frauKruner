<?php

namespace App\Filament\Resources\Verifications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VerificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('street')
                    ->required(),
                TextInput::make('house_no')
                    ->required(),
                TextInput::make('city')
                    ->required(),
                TextInput::make('zip')
                    ->required(),
                DatePicker::make('date_of_birth')
                    ->required(),
                TextInput::make('person_id_shot_img'),
                TextInput::make('id_card_front_img'),
                TextInput::make('id_card_back_img'),
                TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
