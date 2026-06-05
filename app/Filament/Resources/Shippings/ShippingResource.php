<?php

namespace App\Filament\Resources\Shippings;

use App\Filament\Resources\Shippings\Pages\CreateShipping;
use App\Filament\Resources\Shippings\Pages\EditShipping;
use App\Filament\Resources\Shippings\Pages\ListShippings;
use App\Filament\Resources\Shippings\Schemas\ShippingForm;
use App\Filament\Resources\Shippings\Tables\ShippingsTable;
use App\Shipping;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShippingResource extends BaseAdminResource
{
    protected static ?string $model = Shipping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
     protected static bool $shouldRegisterNavigation = false;

    protected static string|\UnitEnum|null $navigationGroup = 'System Management';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    

    public static function form(Schema $schema): Schema
    {
        return ShippingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingsTable::configure($table);
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
            'index' => ListShippings::route('/'),
            'create' => CreateShipping::route('/create'),
            'edit' => EditShipping::route('/{record}/edit'),
        ];
    }
}



