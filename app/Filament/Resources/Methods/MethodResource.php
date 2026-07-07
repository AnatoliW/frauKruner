<?php

namespace App\Filament\Resources\Methods;

use App\Filament\Resources\Methods\Pages\CreateMethod;
use App\Filament\Resources\Methods\Pages\EditMethod;
use App\Filament\Resources\Methods\Pages\ListMethods;
use App\Filament\Resources\Methods\Schemas\MethodForm;
use App\Filament\Resources\Methods\Tables\MethodsTable;
use App\Models\Method;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MethodResource extends BaseAdminResource
{
    protected static ?string $model = Method::class;

    protected static ?string $navigationLabel = 'Bankverbindungen';

    protected static ?string $modelLabel = 'Bankverbindung';

    protected static ?string $pluralModelLabel = 'Bankverbindungen';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MethodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MethodsTable::configure($table);
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
            'index' => ListMethods::route('/'),
            'create' => CreateMethod::route('/create'),
            'edit' => EditMethod::route('/{record}/edit'),
        ];
    }
}

