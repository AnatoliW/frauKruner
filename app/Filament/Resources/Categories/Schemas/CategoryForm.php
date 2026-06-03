<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('parent_id')
                    ->numeric(),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('featured')
                    ->numeric(),
                FileUpload::make('image')
                    ->image(),
                TextInput::make('title'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('color')
                    ->columnSpanFull(),
                Textarea::make('font')
                    ->columnSpanFull(),
            ]);
    }
}
