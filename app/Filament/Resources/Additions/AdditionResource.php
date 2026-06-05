<?php

namespace App\Filament\Resources\Additions;

use App\Addition;
use App\Filament\Resources\Additions\Pages\CreateAddition;
use App\Filament\Resources\Additions\Pages\EditAddition;
use App\Filament\Resources\Additions\Pages\ListAdditions;
use App\Filament\Resources\Additions\Pages\ViewAddition;
use App\Filament\Resources\Additions\Schemas\AdditionForm;
use App\Filament\Resources\Additions\Tables\AdditionsTable;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdditionResource extends BaseAdminResource
{
    protected static ?string $model = Addition::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ? string $navigationLabel = 'Zusatzoptionen';
    protected static string|\UnitEnum|null $navigationGroup = 'Produkte';

    public static function form(Schema $schema): Schema
    {
        return AdditionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdditionsTable::configure($table);
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
            'index' => ListAdditions::route('/'),
            'create' => CreateAddition::route('/create'),
            'view' => ViewAddition::route('/{record}'),
            'edit' => EditAddition::route('/{record}/edit'),
        ];
    }
}



