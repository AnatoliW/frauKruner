<?php

namespace App\Filament\Resources\Postcats;

use App\Filament\Resources\Postcats\Pages\CreatePostcat;
use App\Filament\Resources\Postcats\Pages\EditPostcat;
use App\Filament\Resources\Postcats\Pages\ListPostcats;
use App\Filament\Resources\Postcats\Schemas\PostcatForm;
use App\Filament\Resources\Postcats\Tables\PostcatsTable;
use App\Postcat;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostcatResource extends BaseAdminResource
{
    protected static ?string $model = Postcat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PostcatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostcatsTable::configure($table);
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
            'index' => ListPostcats::route('/'),
            'create' => CreatePostcat::route('/create'),
            'edit' => EditPostcat::route('/{record}/edit'),
        ];
    }
}



