<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code'),
                TextInput::make('discount')
                    ->numeric(),
                DatePicker::make('expire_at'),
                TextInput::make('limit')
                    ->numeric(),
                TextInput::make('minimum_cart')
                    ->numeric(),
                TextInput::make('used')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
