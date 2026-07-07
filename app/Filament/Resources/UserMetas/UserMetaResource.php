<?php

namespace App\Filament\Resources\UserMetas;

use App\Filament\Resources\UserMetas\Pages\CreateUserMeta;
use App\Filament\Resources\UserMetas\Pages\EditUserMeta;
use App\Filament\Resources\UserMetas\Pages\ListUserMetas;
use App\Filament\Resources\UserMetas\Schemas\UserMetaForm;
use App\Filament\Resources\UserMetas\Tables\UserMetasTable;
use App\Models\UserMeta;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserMetaResource extends BaseAdminResource
{
    protected static ?string $model = UserMeta::class;

    protected static ?string $navigationLabel = 'Nutzer-Metadaten';

    protected static ?string $modelLabel = 'Nutzer-Metadatum';

    protected static ?string $pluralModelLabel = 'Nutzer-Metadaten';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserMetaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserMetasTable::configure($table);
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
            'index' => ListUserMetas::route('/'),
            'create' => CreateUserMeta::route('/create'),
            'edit' => EditUserMeta::route('/{record}/edit'),
        ];
    }
}

