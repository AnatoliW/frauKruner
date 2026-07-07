<?php

namespace App\Filament\Resources\Shippings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShippingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('Shipping_method'),
                TextInput::make('shipping_cost')
                    ->numeric()
                    ->suffix(' €'),
            ]);
    }
}
