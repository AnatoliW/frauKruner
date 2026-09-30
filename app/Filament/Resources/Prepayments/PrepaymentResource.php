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

    protected static ?string $navigationLabel = 'Vorkasse';

    protected static ?string $modelLabel = 'Vorkasse';

    protected static ?string $pluralModelLabel = 'Vorkasse';

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static string|\UnitEnum|null $navigationGroup = 'Zahlungen';

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
        // Eine Zeile je Bestellung, nicht je Position: Bezahlt wird die
        // Bestellung als Ganzes, also gibt es hier auch nur einen Knopf dafür.
        // Vorher stand eine Bestellung mit zwei Artikeln zweimal in der Liste
        // und musste zweimal als bezahlt markiert werden.
        //
        // Die Positionen hängen als childrens daran und werden vorgeladen, weil
        // die Tabelle Produkt und Verkäuferin je Position anzeigt.
        return parent::getEloquentQuery()
            ->filter()
            ->whereNull('parent_id')
            ->where('payment_status', 0)
            ->where('payment_gateway', 'pre_payment')
            ->with(['childrens.product', 'childrens.vendor'])
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
