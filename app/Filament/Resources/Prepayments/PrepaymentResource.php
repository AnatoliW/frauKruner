<?php

namespace App\Filament\Resources\Prepayments;

use App\Filament\Resources\BaseAdminResource;
use App\Filament\Resources\Prepayments\Pages\ListPrepayments;
use App\Filament\Resources\Prepayments\Tables\PrepaymentsTable;
use App\Order;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrepaymentResource extends BaseAdminResource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationLabel = 'Prepayments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return PrepaymentsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->filter()
            ->children()
            ->where('payment_status', 0)
            ->where('payment_gateway', 'pre_payment')
            ->latest(Order::CREATED_AT);
    }

    public static function getNavigationSort(): ?int
    {
        return 19;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrepayments::route('/'),
        ];
    }
}
