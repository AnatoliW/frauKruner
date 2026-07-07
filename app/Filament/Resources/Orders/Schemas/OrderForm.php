<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('parent_id')
                    ->numeric(),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('vendor_id')
                    ->numeric(),
                TextInput::make('product_id')
                    ->numeric(),
                TextInput::make('first_name'),
                TextInput::make('last_name'),
                TextInput::make('additional'),
                TextInput::make('street'),
                TextInput::make('house_no'),
                TextInput::make('zip'),
                TextInput::make('federal_state'),
                TextInput::make('po_box'),
                TextInput::make('email')
                    ->label('E-Mail-Adresse')
                    ->email(),
                TextInput::make('discount')
                    ->numeric(),
                TextInput::make('discount_code'),
                TextInput::make('subtotal')
                    ->numeric()
                    ->suffix(' €'),
                TextInput::make('tax')
                    ->numeric()
                    ->suffix(' €'),
                TextInput::make('shipping_cost')
                    ->numeric()
                    ->suffix(' €'),
                TextInput::make('shipping_method'),
                TextInput::make('tracking_Id'),
                TextInput::make('total')
                    ->numeric()
                    ->suffix(' €'),
                Textarea::make('wearing_time')
                    ->columnSpanFull(),
                Textarea::make('finishings')
                    ->columnSpanFull(),
                Textarea::make('addition')
                    ->columnSpanFull(),
                TextInput::make('payment_gateway'),
                TextInput::make('shipped')
                    ->numeric(),
                DateTimePicker::make('video_uploaded_at'),
                TextInput::make('status')
                    ->numeric(),
                Textarea::make('message')
                    ->columnSpanFull(),
                TextInput::make('payouts_status')
                    ->numeric()
                    ->default(0),
                TextInput::make('payouts_rerquest')
                    ->numeric()
                    ->default(0),
                TextInput::make('vendor_total')
                    ->numeric()
                    ->suffix(' €'),
                TextInput::make('commission')
                    ->numeric()
                    ->suffix(' €'),
                TextInput::make('photo'),
                TextInput::make('video'),
                DatePicker::make('shipping_date')
                    ->label('Versanddatum')
                    ->displayFormat('d.m.Y'),
                TextInput::make('send_shipping_email')
                    ->email()
                    ->numeric(),
                TextInput::make('payment_id'),
                TextInput::make('payment_status')
                    ->numeric()
                    ->default(0),
                TextInput::make('is_rated')
                    ->numeric()
                    ->default(0),
                TextInput::make('seller_info'),
                TextInput::make('product_name'),
                Toggle::make('meta_remove_status')
                    ->required(),
                Textarea::make('meta_remove_log')
                    ->columnSpanFull(),
            ]);
    }
}
