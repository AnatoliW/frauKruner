<?php

namespace App\Filament\Resources\ProdcatProducts;

use App\Filament\Resources\ProdcatProducts\Pages\CreateProdcatProduct;
use App\Filament\Resources\ProdcatProducts\Pages\EditProdcatProduct;
use App\Filament\Resources\ProdcatProducts\Pages\ListProdcatProducts;
use App\Filament\Resources\ProdcatProducts\Schemas\ProdcatProductForm;
use App\Filament\Resources\ProdcatProducts\Tables\ProdcatProductsTable;
use App\ProdcatProduct;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProdcatProductResource extends BaseAdminResource
{
    protected static ?string $model = ProdcatProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ProdcatProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProdcatProductsTable::configure($table);
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
            'index' => ListProdcatProducts::route('/'),
            'create' => CreateProdcatProduct::route('/create'),
            'edit' => EditProdcatProduct::route('/{record}/edit'),
        ];
    }
}



