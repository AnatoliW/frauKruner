<?php

namespace App\Filament\Resources\Payouts;

use App\Filament\Resources\BaseAdminResource;
use App\Filament\Resources\Payouts\Pages\ListPayouts;
use App\Filament\Resources\Payouts\Tables\PayoutsTable;
use App\Order;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayoutResource extends BaseAdminResource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationLabel = 'Auszahlungen';

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Zahlungen';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return PayoutsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->paid()
            ->active()
            ->children()
            ->filter()
            ->latest(Order::CREATED_AT);
    }

    public static function getNavigationSort(): ?int
    {
        return 18;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayouts::route('/'),
        ];
    }
}
