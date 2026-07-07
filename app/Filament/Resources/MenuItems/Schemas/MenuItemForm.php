<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Models\Menu;
use App\Models\MenuItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('menu_id')
                    ->label('Menü')
                    ->options(Menu::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->maxLength(255),
                TextInput::make('route')
                    ->maxLength(255),
                TextInput::make('target')
                    ->default('_self')
                    ->maxLength(255),
                TextInput::make('icon_class')
                    ->maxLength(255),
                TextInput::make('color')
                    ->maxLength(255),
                Select::make('parent_id')
                    ->label('Übergeordneter Eintrag')
                    ->options(MenuItem::query()->orderBy('title')->pluck('title', 'id'))
                    ->searchable(),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(1),
                Textarea::make('parameters')
                    ->columnSpanFull(),
            ]);
    }
}
