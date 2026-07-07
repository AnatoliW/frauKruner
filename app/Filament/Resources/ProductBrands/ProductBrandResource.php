<?php

namespace App\Filament\Resources\ProductBrands;

use App\Filament\Resources\ProductBrands\Pages\CreateProductBrand;
use App\Filament\Resources\ProductBrands\Pages\EditProductBrand;
use App\Filament\Resources\ProductBrands\Pages\ListProductBrands;
use App\Filament\Resources\ProductBrands\Schemas\ProductBrandForm;
use App\Filament\Resources\ProductBrands\Tables\ProductBrandsTable;
use App\ProductBrand;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductBrandResource extends BaseAdminResource
{
    protected static ?string $model = ProductBrand::class;

    protected static ?string $navigationLabel = 'Produktmarken';

    protected static ?string $modelLabel = 'Produktmarke';

    protected static ?string $pluralModelLabel = 'Produktmarken';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ProductBrandForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductBrandsTable::configure($table);
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
            'index' => ListProductBrands::route('/'),
            'create' => CreateProductBrand::route('/create'),
            'edit' => EditProductBrand::route('/{record}/edit'),
        ];
    }
}



