<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('category_id')
                    ->numeric(),
                TextInput::make('parent_id')
                    ->numeric(),
                TextInput::make('name'),
                TextInput::make('slug'),
                TextInput::make('price')
                    ->numeric()
                    ->suffix(' €'),
                TextInput::make('saleprice')
                    ->numeric()
                    ->suffix(' €'),
                Textarea::make('details')
                    ->columnSpanFull(),
                TextInput::make('quantity')
                    ->numeric(),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image()
                    ->disk('s3')
                    ->directory('products'),
                TextInput::make('view')
                    ->numeric()
                    ->default(0),
                TextInput::make('sale_count')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('is_variable')
                    ->numeric(),
                TextInput::make('tags'),
                Textarea::make('finishings')
                    ->columnSpanFull(),
                Textarea::make('addition')
                    ->columnSpanFull(),
                Textarea::make('wearing_time')
                    ->columnSpanFull(),
                TextInput::make('shipping_cost')
                    ->numeric()
                    ->suffix(' €'),
                TextInput::make('featured')
                    ->numeric(),
                TextInput::make('discount')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('selloption')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('boosted')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('boost_start_date'),
                DateTimePicker::make('boost_end_date'),
                Toggle::make('meta_remove_status')
                    ->required(),
                Textarea::make('meta_remove_log')
                    ->columnSpanFull(),
            ]);
    }
}
