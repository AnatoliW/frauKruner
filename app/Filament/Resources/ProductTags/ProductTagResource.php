<?php

namespace App\Filament\Resources\ProductTags;

use App\Filament\Resources\ProductTags\Pages\CreateProductTag;
use App\Filament\Resources\ProductTags\Pages\EditProductTag;
use App\Filament\Resources\ProductTags\Pages\ListProductTags;
use App\Filament\Resources\ProductTags\Schemas\ProductTagForm;
use App\Filament\Resources\ProductTags\Tables\ProductTagsTable;
use App\ProductTag;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductTagResource extends BaseAdminResource
{
    protected static ?string $model = ProductTag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ProductTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductTagsTable::configure($table);
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
            'index' => ListProductTags::route('/'),
            'create' => CreateProductTag::route('/create'),
            'edit' => EditProductTag::route('/{record}/edit'),
        ];
    }
}



