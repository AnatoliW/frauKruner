<?php

namespace App\Filament\Resources\Prodcats\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProdcatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('slug'),
                TextInput::make('parent_id')
                    ->numeric(),
            ]);
    }
}
