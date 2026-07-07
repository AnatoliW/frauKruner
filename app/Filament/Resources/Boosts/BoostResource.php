<?php

namespace App\Filament\Resources\Boosts;

use App\Filament\Resources\Boosts\Pages\CreateBoost;
use App\Filament\Resources\Boosts\Pages\EditBoost;
use App\Filament\Resources\Boosts\Pages\ListBoosts;
use App\Filament\Resources\Boosts\Pages\ViewBoost;
use App\Filament\Resources\Boosts\Schemas\BoostForm;
use App\Filament\Resources\Boosts\Tables\BoostsTable;
use App\Filament\Resources\BaseAdminResource;
use App\Models\Boost;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BoostResource extends BaseAdminResource
{
    protected static ?string $model = Boost::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ? string $navigationLabel = 'Push-Übersicht';

    protected static ?string $modelLabel = 'Push-Eintrag';

    protected static ?string $pluralModelLabel = 'Push-Übersicht';

    protected static string|\UnitEnum|null $navigationGroup = 'Push';

    public static function form(Schema $schema): Schema
    {
        return BoostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoostsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->filter()
            ->paid()
            ->latest();
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
            'invoice' => ViewBoost::route('/{record}/invoice'),
        ];
    }
}

