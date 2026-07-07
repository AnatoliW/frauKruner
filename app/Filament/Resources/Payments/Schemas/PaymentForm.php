<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('payable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('payable_type')
                    ->required(),
                TextInput::make('payment_trnx_id'),
                TextInput::make('payment_method'),
                Select::make('status')
                    ->options(['PAID' => 'P a i d', 'PENDING' => 'P e n d i n g', 'CANCELED' => 'C a n c e l e d'])
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->suffix(' €'),
                TextInput::make('tax')
                    ->numeric()
                    ->default(0)
                    ->suffix(' €'),
                Textarea::make('response_body')
                    ->columnSpanFull(),
            ]);
    }
}
