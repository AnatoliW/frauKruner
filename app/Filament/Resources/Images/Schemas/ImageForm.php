<?php

namespace App\Filament\Resources\Images\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                Textarea::make('image')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('nsfw')
                    ->numeric()
                    ->default(0),
                Toggle::make('meta_remove_status')
                    ->required(),
                Textarea::make('meta_remove_log')
                    ->columnSpanFull(),
            ]);
    }
}
