<?php

namespace App\Filament\Resources\Points;

use App\Filament\Resources\Points\Pages\CreatePoint;
use App\Filament\Resources\Points\Pages\EditPoint;
use App\Filament\Resources\Points\Pages\ListPoints;
use App\Filament\Resources\Points\Schemas\PointForm;
use App\Filament\Resources\Points\Tables\PointsTable;
use App\Models\Point;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PointResource extends BaseAdminResource
{
    protected static ?string $model = Point::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PointForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PointsTable::configure($table);
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
            'index' => ListPoints::route('/'),
            'create' => CreatePoint::route('/create'),
            'edit' => EditPoint::route('/{record}/edit'),
        ];
    }
}

