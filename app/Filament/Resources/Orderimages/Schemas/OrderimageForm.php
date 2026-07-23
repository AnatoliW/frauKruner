<?php

namespace App\Filament\Resources\Orderimages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderimageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_id')
                    ->required()
                    ->numeric(),
                FileUpload::make('image')
                    ->image()
                    ->disk('s3')
                    ->directory('orderimages')
                    ->required(),
                Toggle::make('meta_remove_status')
                    ->required(),
                Textarea::make('meta_remove_log')
                    ->columnSpanFull(),
            ]);
    }
}
