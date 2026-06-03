<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('question'),
                Textarea::make('answer')
                    ->columnSpanFull(),
                TextInput::make('type')
                    ->numeric(),
            ]);
    }
}
