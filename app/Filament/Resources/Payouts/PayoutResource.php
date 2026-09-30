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

    protected static ?string $modelLabel = 'Auszahlung';

    protected static ?string $pluralModelLabel = 'Auszahlungen';

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

    /**
     * Diese Liste zeigt Positionen: je Verkäuferin eine Auszahlung.
     *
     * Vorgeladen wird alles, was die Tabelle je Zeile anzeigt. Ohne das holt
     * jede Zeile ihre Bestellung, ihre Verkäuferin, deren Bankverbindung, ihr
     * Produkt, dessen Kategorie und ihre Fotos einzeln nach – gemessen sechs
     * Abfragen je Zeile, also knapp 190 für eine Seite mit 30 Auszahlungen.
     * Auf einer Datenbank im selben Rechner fällt das nicht auf; liegt sie auf
     * einem anderen Host, kostet jede dieser Abfragen ihren Netzweg, und aus
     * Millisekunden werden Sekunden. Genau das war live als lange Wartezeit
     * nach dem Tippen im Suchfeld zu sehen.
     *
     * `parent` gehört dazu, weil die Bestellnummer der Position aus dem Kopf
     * der Bestellung kommt (Order::orderNumber() über mainOrder()).
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->paid()
            ->active()
            ->children()
            ->filter()
            ->with([
                'parent',
                'vendor.method',
                'product.category',
                'orderimages',
            ])
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
