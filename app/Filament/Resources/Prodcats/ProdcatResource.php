<?php

namespace App\Filament\Resources\Prodcats;

use App\Filament\Resources\Prodcats\Pages\CreateProdcat;
use App\Filament\Resources\Prodcats\Pages\EditProdcat;
use App\Filament\Resources\Prodcats\Pages\ListProdcats;
use App\Filament\Resources\Prodcats\Schemas\ProdcatForm;
use App\Filament\Resources\Prodcats\Tables\ProdcatsTable;
use App\Prodcat;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProdcatResource extends BaseAdminResource
{
    protected static ?string $model = Prodcat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

     protected static bool $shouldRegisterNavigation = false;

    protected static string|\UnitEnum|null $navigationGroup = 'System Management';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ProdcatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProdcatsTable::configure($table);
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
            'index' => ListProdcats::route('/'),
            'create' => CreateProdcat::route('/create'),
            'edit' => EditProdcat::route('/{record}/edit'),
        ];
    }
}



