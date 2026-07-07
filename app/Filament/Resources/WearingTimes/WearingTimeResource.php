<?php

namespace App\Filament\Resources\WearingTimes;

use App\Filament\Resources\WearingTimes\Pages\CreateWearingTime;
use App\Filament\Resources\WearingTimes\Pages\EditWearingTime;
use App\Filament\Resources\WearingTimes\Pages\ListWearingTimes;
use App\Filament\Resources\WearingTimes\Schemas\WearingTimeForm;
use App\Filament\Resources\WearingTimes\Tables\WearingTimesTable;
use App\WearingTime;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WearingTimeResource extends BaseAdminResource
{
    protected static ?string $model = WearingTime::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ? string $navigationLabel = 'Tragedauer';

    protected static ?string $modelLabel = 'Tragedauer';

    protected static ?string $pluralModelLabel = 'Tragedauer';
    protected static string|\UnitEnum|null $navigationGroup = 'Produkte';

    public static function form(Schema $schema): Schema
    {
        return WearingTimeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WearingTimesTable::configure($table);
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
            'index' => ListWearingTimes::route('/'),
            'create' => CreateWearingTime::route('/create'),
            'edit' => EditWearingTime::route('/{record}/edit'),
        ];
    }
}



