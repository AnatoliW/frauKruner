<?php

namespace App\Filament\Resources\ProdcatProducts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProdcatProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_id')
                    ->numeric(),
                TextInput::make('prodcat_id')
                    ->numeric(),
            ]);
    }
}
