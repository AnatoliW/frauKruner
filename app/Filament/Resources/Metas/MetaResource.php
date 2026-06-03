<?php

namespace App\Filament\Resources\Metas;

use App\Filament\Resources\Metas\Pages\CreateMeta;
use App\Filament\Resources\Metas\Pages\EditMeta;
use App\Filament\Resources\Metas\Pages\ListMetas;
use App\Filament\Resources\Metas\Schemas\MetaForm;
use App\Filament\Resources\Metas\Tables\MetasTable;
use App\Models\Meta;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MetaResource extends BaseAdminResource
{
    protected static ?string $model = Meta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MetaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MetasTable::configure($table);
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
            'index' => ListMetas::route('/'),
            'create' => CreateMeta::route('/create'),
            'edit' => EditMeta::route('/{record}/edit'),
        ];
    }
}

