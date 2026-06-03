<?php

namespace App\Filament\Resources\Boosts;

use App\Filament\Resources\Boosts\Pages\CreateBoost;
use App\Filament\Resources\Boosts\Pages\EditBoost;
use App\Filament\Resources\Boosts\Pages\ListBoosts;
use App\Filament\Resources\Boosts\Schemas\BoostForm;
use App\Filament\Resources\Boosts\Tables\BoostsTable;
use App\Models\Boost;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BoostResource extends BaseAdminResource
{
    protected static ?string $model = Boost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BoostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoostsTable::configure($table);
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
            'index' => ListBoosts::route('/'),
            'create' => CreateBoost::route('/create'),
            'edit' => EditBoost::route('/{record}/edit'),
        ];
    }
}

