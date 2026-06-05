<?php

namespace App\Filament\Resources\Finishings;

use App\Filament\Resources\Finishings\Pages\CreateFinishing;
use App\Filament\Resources\Finishings\Pages\EditFinishing;
use App\Filament\Resources\Finishings\Pages\ListFinishings;
use App\Filament\Resources\Finishings\Pages\ViewFinishing;
use App\Filament\Resources\Finishings\Schemas\FinishingForm;
use App\Filament\Resources\Finishings\Tables\FinishingsTable;
use App\Finishing;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FinishingResource extends BaseAdminResource
{
    protected static ?string $model = Finishing::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ? string $navigationLabel = 'Veredlungen';
    protected static string|\UnitEnum|null $navigationGroup = 'Produkte';

    public static function form(Schema $schema): Schema
    {
        return FinishingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinishingsTable::configure($table);
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
            'index' => ListFinishings::route('/'),
            'create' => CreateFinishing::route('/create'),
            'view' => ViewFinishing::route('/{record}'),
            'edit' => EditFinishing::route('/{record}/edit'),
        ];
    }
}



