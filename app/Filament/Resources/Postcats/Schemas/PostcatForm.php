<?php

namespace App\Filament\Resources\Postcats\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostcatForm
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
