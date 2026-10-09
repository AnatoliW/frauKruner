<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\OrderResource;
use App\Order;
use App\Support\OrderNumberSearch;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

/**
 * Die Bestellliste zeigt Bestellungen, nicht Positionen.
 *
 * Eine Bestellung besteht aus einem Kopf und je einer Position pro
 * Warenkorbposition. Alle Spalten hier stehen deshalb für die Bestellung als
 * Ganzes; was je Position gilt – Artikel, Verkäuferin, Versand, Auszahlung –
 * steht mehrzeilig in der Zelle. Die Einzelheiten und die Belege liegen in der
 * Ansicht der Bestellung.
 */
class OrdersTable
{
    /**
     * Die Positionen einer Bestellung. Eine Bestellung ohne Positionen
     * (Altdatensatz) ist ihre eigene einzige Position.
     *
     * @return \Illuminate\Support\Collection<int, Order>
     */
    private static function positions(Order $order)
    {
        $positions = $order->childrens;

        return $positions->isNotEmpty() ? $positions : collect([$order]);
    }

    /**
     * Mehrzeilige Zelle: je Position eine Zeile, HTML-sicher.
     *
     * @param  callable(Order): ?string  $line  Bereits maskierter Inhalt
     */
    private static function perPosition(Order $order, callable $line): string
    {
        $lines = static::positions($order)
            ->map($line)
            ->filter(fn ($value) => filled($value));

        return $lines->isEmpty() ? '-' : $lines->implode('<br>');
    }

    public static function configure(Table $table): Table
    {
        return $table
            // Eine Zeile je Bestellung, nicht je Position. Die Artikel stehen
            // mehrzeilig in der Zelle und ausführlich in der Ansicht.
            //
            // Nur die Liste wird so eingeschränkt, nicht die Abfrage der
            // Ressource: Diese löst auch Positions-IDs auf, damit gespeicherte
            // Links von früher (`/admin/orders/3552`) weiter funktionieren.
            // Siehe OrderResource::getEloquentQuery().
            ->modifyQueryUsing(fn (Builder $query) => OrderResource::scopeToOrders($query))
            ->defaultSort('created_at', 'desc')
            ->columns([
                // Die Nummer, die die Kundin bezahlt hat und die auf dem
                // Kontoauszug steht – danach wird eine Bestellung gesucht.
                TextColumn::make('order_number')
                    ->label('Bestellnummer')
                    ->weight('bold')
                    ->state(fn (Order $record): string => $record->orderNumber())
                    ->description(function (Order $record): ?string {
                        $count = $record->childrens->count();

                        return $count > 1 ? $count.' Artikel' : null;
                    })
                    // Exakter Vergleich statt LIKE: Er nutzt den Index und trifft
                    // nicht auch 112696, wenn nach 12696 gesucht wird. `FK2026-12696`
                    // darf direkt aus Mail oder Kontoauszug eingefügt werden.
                    ->searchable(query: function ($query, string $search): void {
                        if (! $nummer = OrderNumberSearch::number($search)) {
                            return;
                        }

                        // Auch die Beleg-Nr. einer Position findet ihre Bestellung –
                        // die Rechnung des Kunden nennt genau diese Nummer.
                        $query->where(function ($q) use ($nummer): void {
                            $q->where('id', $nummer)
                                ->orWhereHas('childrens', fn ($c) => $c->where('id', $nummer));
                        });
                    }),
                TextColumn::make('buyer')
                    ->label('Käufer')
                    ->html()
                    ->state(function (Order $record): string {
                        $name = trim(($record->first_name ?? '').' '.($record->last_name ?? ''));
                        $email = trim((string) ($record->email ?? ''));

                        if ($email === '') {
                            return e($name) ?: '-';
                        }

                        return e($name).'<br><a href="mailto:'.e($email).'">'.e($email).'</a>';
                    })
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search): void {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('user_id')
                    ->label('Nutzer ID')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                // Artikel und Verkäuferin hängen an den Positionen: Bei mehreren
                // Artikeln stehen hier mehrere Zeilen untereinander.
                //
                // Unter dem Artikel steht seine Belegnummer: Ein Beleg gilt je
                // Position, und das ist die Nummer, die auf Rechnung und
                // Gutschrift steht – nicht die Bestellnummer darüber.
                TextColumn::make('articles')
                    ->label('Artikel')
                    ->html()
                    ->state(fn (Order $record): string => static::perPosition(
                        $record,
                        fn (Order $position): ?string => e(
                            $position->product_name ?? $position->product?->name ?? '-'
                        ).'<br><small style="opacity:.6;">Beleg-Nr. '
                            .e($position->invoiceNumber()).'</small>'
                    )),
                TextColumn::make('vendors')
                    ->label('Verkaeuferin')
                    ->html()
                    ->state(fn (Order $record): string => static::perPosition(
                        $record,
                        function (Order $position): ?string {
                            $name = trim(($position->vendor->name ?? '').' '.($position->vendor->last_name ?? ''));

                            return $name !== '' ? e($name) : null;
                        }
                    ))
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('childrens.vendor', function ($q) use ($search): void {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('total')
                    ->label('Gesamt')
                    ->money()
                    ->sortable()
                    // Im Kopf der Bestellung ist der Gutschein bereits abgezogen
                    // (CheckoutController::processOrder() legt total als
                    // subtotal − discount an). Hier darf nicht erneut abgezogen
                    // werden; der Wert ist der bezahlte Betrag.
                    ->description(function (Order $record, Table $table): ?Htmlable {
                        $discount = (float) ($record->discount ?? 0);

                        if ($discount <= 0 || blank($record->discount_code)) {
                            return null;
                        }

                        $currency = $table->getDefaultCurrency();
                        $locale = $table->getDefaultNumberLocale() ?? config('app.locale');

                        return new HtmlString(
                            'Zwischensumme: '.e(Number::currency((float) $record->subtotal, $currency, $locale))
                            .'<br>Gutschein '.e($record->discount_code)
                            .': −'.e(Number::currency($discount, $currency, $locale))
                        );
                    }),
                TextColumn::make('payouts_status')
                    ->label(new HtmlString('Status der<br>Auszahlung'))
                    ->wrapHeader()
                    ->badge()
                    // Ausgezahlt wird je Position, denn jede Verkäuferin bekommt
                    // ihr Geld einzeln. Die Bestellung ist deshalb entweder ganz,
                    // teilweise oder nicht ausgezahlt.
                    ->state(function (Order $record): string {
                        $positions = static::positions($record);
                        $paid = $positions->filter(fn (Order $position): bool => (int) ($position->payouts_status ?? 0) === 1)->count();

                        if ($paid === 0) {
                            return 'Nicht ausgezahlt';
                        }

                        if ($paid === $positions->count()) {
                            return 'Ausgezahlt';
                        }

                        return $paid.' von '.$positions->count().' ausgezahlt';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Ausgezahlt' => 'success',
                        'Nicht ausgezahlt' => 'primary',
                        default => 'warning',
                    }),
                TextColumn::make('vendor_total')
                    ->label(new HtmlString('Verkaeuferin<br>bekommt'))
                    ->wrapHeader()
                    ->money()
                    // Summe über die Positionen: Der Kopf der Bestellung trägt
                    // denselben Wert, aber gezahlt wird er je Verkäuferin.
                    ->state(fn (Order $record): float => (float) static::positions($record)->sum('vendor_total')),
                TextColumn::make('commission')
                    ->label('Komision')
                    ->money()
                    ->state(fn (Order $record): float => (float) static::positions($record)->sum('commission')),
                TextColumn::make('shipping')
                    ->label('Versand')
                    ->html()
                    ->state(fn (Order $record): string => static::perPosition(
                        $record,
                        function (Order $position): string {
                            if (blank($position->shipping_date)) {
                                return '<span style="opacity:.6">offen</span>';
                            }

                            return e($position->shipping_date->format('d.m.Y'));
                        }
                    )),
                TextColumn::make('created_at')
                    ->label('Bestelldatum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Ansehen')
                    ->button()
                    ->size('sm')
                    ->color('warning')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
                // Storniert wird je Position und nicht je Bestellung: Eine
                // Stornierung betrifft eine Verkäuferin, die nicht geliefert hat,
                // und schickt genau ihr eine Nachricht. Die Knöpfe dafür stehen
                // deshalb in der Ansicht der Bestellung, je Position.
                Action::make('cancelled')
                    ->label('Storniert')
                    ->button()
                    ->size('sm')
                    ->color('gray')
                    ->disabled()
                    ->visible(fn (Order $record): bool => static::positions($record)
                        ->every(fn (Order $position): bool => (int) ($position->status ?? 0) === 3)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
