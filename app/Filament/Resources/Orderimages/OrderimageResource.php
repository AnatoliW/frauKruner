<?php

namespace App\Filament\Resources\Orderimages;

use App\Filament\Resources\Orderimages\Pages\CreateOrderimage;
use App\Filament\Resources\Orderimages\Pages\EditOrderimage;
use App\Filament\Resources\Orderimages\Pages\ListOrderimages;
use App\Filament\Resources\Orderimages\Schemas\OrderimageForm;
use App\Filament\Resources\Orderimages\Tables\OrderimagesTable;
use App\Models\Orderimage;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderimageResource extends BaseAdminResource
{
    protected static ?string $model = Orderimage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OrderimageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderimagesTable::configure($table);
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
            'index' => ListOrderimages::route('/'),
            'create' => CreateOrderimage::route('/create'),
            'edit' => EditOrderimage::route('/{record}/edit'),
        ];
    }
}

