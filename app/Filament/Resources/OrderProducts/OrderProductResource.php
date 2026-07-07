<?php

namespace App\Filament\Resources\OrderProducts;

use App\Filament\Resources\OrderProducts\Pages\CreateOrderProduct;
use App\Filament\Resources\OrderProducts\Pages\EditOrderProduct;
use App\Filament\Resources\OrderProducts\Pages\ListOrderProducts;
use App\Filament\Resources\OrderProducts\Schemas\OrderProductForm;
use App\Filament\Resources\OrderProducts\Tables\OrderProductsTable;
use App\OrderProduct;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderProductResource extends BaseAdminResource
{
    protected static ?string $model = OrderProduct::class;

    protected static ?string $navigationLabel = 'Bestellpositionen';

    protected static ?string $modelLabel = 'Bestellposition';

    protected static ?string $pluralModelLabel = 'Bestellpositionen';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OrderProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderProducts::route('/'),
            'create' => CreateOrderProduct::route('/create'),
            'edit' => EditOrderProduct::route('/{record}/edit'),
        ];
    }
}



