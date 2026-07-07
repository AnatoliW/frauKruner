<?php

namespace App\Filament\Resources\Favorites;

use App\Filament\Resources\Favorites\Pages\CreateFavorite;
use App\Filament\Resources\Favorites\Pages\EditFavorite;
use App\Filament\Resources\Favorites\Pages\ListFavorites;
use App\Filament\Resources\Favorites\Schemas\FavoriteForm;
use App\Filament\Resources\Favorites\Tables\FavoritesTable;
use App\Models\Favorite;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FavoriteResource extends BaseAdminResource
{
    protected static ?string $model = Favorite::class;

    protected static ?string $navigationLabel = 'Favoriten';

    protected static ?string $modelLabel = 'Favorit';

    protected static ?string $pluralModelLabel = 'Favoriten';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return FavoriteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FavoritesTable::configure($table);
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
            'index' => ListFavorites::route('/'),
            'create' => CreateFavorite::route('/create'),
            'edit' => EditFavorite::route('/{record}/edit'),
        ];
    }
}

