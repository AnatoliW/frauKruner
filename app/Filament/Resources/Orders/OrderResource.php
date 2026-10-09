<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Order;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderResource extends BaseAdminResource
{
    protected static ?string $model = Order::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Bestellungen';

    protected static ?string $modelLabel = 'Bestellung';

    protected static ?string $pluralModelLabel = 'Bestellungen';

    protected static string|\UnitEnum|null $navigationGroup = 'Zahlungen';

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    /**
     * Diese Abfrage entscheidet auch, welche Datensätze die Ansicht über ihre
     * Adresse auflösen darf – deshalb sind hier Bestellköpfe UND Positionen
     * zugelassen.
     *
     * Die Liste zeigte früher die Positionen; gespeicherte Links und Lesezeichen
     * tragen deshalb noch eine Positions-ID (`/admin/orders/3552`). Würde die
     * Abfrage auf Köpfe eingeschränkt, liefen die alle in einen 404. Die Ansicht
     * löst eine Position selbst auf ihre Bestellung auf und zeigt die Belege
     * dieser Bestellung.
     *
     * Dass die LISTE nur eine Zeile je Bestellung zeigt, regelt stattdessen
     * OrdersTable über modifyQueryUsing(): Vorher stand eine Bestellung mit zwei
     * Artikeln zweimal darin – mit zwei verschiedenen Nummern, von denen keine
     * die bezahlte war.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->paid()
            ->latest(Order::CREATED_AT);
    }

    /**
     * Schränkt eine Abfrage auf Bestellungen ein – eine Zeile je Bestellung,
     * nicht je Position.
     *
     * Einzige Quelle dieser Einschränkung: Die Liste wendet sie über
     * OrdersTable an, und die Tests prüfen sie hier. Lägen beide Seiten
     * getrennt, könnte die Liste wieder Positionen zeigen, ohne dass ein Test
     * es merkt.
     *
     * Vorgeladen wird alles, was die Liste je Position anzeigt; sonst holt jede
     * Zeile ihre Positionen, Produkte und Verkäuferinnen einzeln nach.
     */
    public static function scopeToOrders(Builder $query): Builder
    {
        return $query
            ->whereNull('parent_id')
            ->with(['childrens.product', 'childrens.vendor']);
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
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}



