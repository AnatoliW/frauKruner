<?php

namespace App\Filament\Resources\OrderProducts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_id')
                    ->numeric(),
                TextInput::make('product_id')
                    ->numeric(),
                TextInput::make('quantity')
                    ->numeric(),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('$'),
                Textarea::make('variation')
                    ->columnSpanFull(),
            ]);
    }
}
