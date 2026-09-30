<?php

namespace App\Filament\Resources\Prepayments\Tables;

use App\Order;
use App\Support\OrderNumberSearch;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class PrepaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // Die Nummer, die die Kundin überwiesen hat und die im
                // Verwendungszweck steht – danach wird dieser Eingang gesucht.
                TextColumn::make('order_number')
                    ->label('Bestellnummer')
                    ->weight('bold')
                    ->state(fn (Order $record): string => $record->orderNumber())
                    ->description(fn (Order $record): ?string => ($count = $record->childrens->count()) > 1
                        ? $count.' Positionen'
                        : null)
                    // Exakter Vergleich statt LIKE: Er nutzt den Index und trifft
                    // nicht auch 112696, wenn nach 12696 gesucht wird. `FK2026-12696`
                    // darf direkt aus Mail oder Kontoauszug eingefügt werden.
                    ->searchable(query: function ($query, string $search): void {
                        if (! $nummer = OrderNumberSearch::number($search)) {
                            return;
                        }

                        // Auch die Beleg-Nr. einer Position findet ihre Bestellung.
                        $query->where(function ($q) use ($nummer): void {
                            $q->where('id', $nummer)
                                ->orWhereHas('childrens', fn ($c) => $c->where('id', $nummer));
                        });
                    }),
                TextColumn::make('user_id')
                    ->label('Nutzer ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('buyer')
                    ->label('Käufer')
                    ->html()
                    ->state(function (Order $record): string {
                        $name = trim(($record->first_name ?? '').' '.($record->last_name ?? ''));
                        $email = trim((string) ($record->email ?? ''));

                        if ($email === '') {
                            return e($name);
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
                // Verkäuferin und Produkt hängen an den Positionen, nicht an der
                // Bestellung: Bei mehreren Artikeln stehen hier mehrere Zeilen.
                TextColumn::make('vendor.name')
                    ->label('Verkäuferin')
                    ->html()
                    ->state(function (Order $record): string {
                        $names = $record->childrens
                            ->map(fn (Order $position): string => trim(
                                ($position->vendor->name ?? '').' '.($position->vendor->last_name ?? '')
                            ))
                            ->filter()
                            ->map(fn (string $name): string => e($name));

                        return $names->isEmpty() ? '-' : $names->implode('<br>');
                    })
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('childrens.vendor', function ($q) use ($search): void {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('product')
                    ->label('Produkt')
                    ->html()
                    ->state(function (Order $record): string {
                        $links = $record->childrens
                            ->map(function (Order $position): ?string {
                                if (! $position->product || ! $position->product->slug) {
                                    return null;
                                }

                                $url = route('product', $position->product->slug);

                                return '<a href="'.e($url).'" target="_blank">'
                                    .e((string) $position->product->name).'</a>';
                            })
                            ->filter();

                        return $links->isEmpty() ? '-' : $links->implode('<br>');
                    }),
                TextColumn::make('total')
                    ->label('Zu zahlen')
                    ->money()
                    ->sortable()
                    // Achtung, anders als bei einer Position: Im Kopf der
                    // Bestellung ist der Gutschein bereits abgezogen
                    // (CheckoutController::processOrder() legt total als
                    // subtotal − discount an). Hier darf also nicht noch einmal
                    // abgezogen werden – der Wert ist direkt der Betrag, den die
                    // Kundin überweisen soll.
                    ->description(function (Order $record, Table $table): ?Htmlable {
                        $discount = (float) ($record->discount ?? 0);

                        if ($discount <= 0 || blank($record->discount_code)) {
                            return null;
                        }

                        // Dieselben Vorgaben, mit denen money() oben formatiert –
                        // sonst stünden zwei verschieden formatierte Beträge
                        // untereinander.
                        $currency = $table->getDefaultCurrency();
                        $locale = $table->getDefaultNumberLocale() ?? config('app.locale');

                        return new HtmlString(
                            'Zwischensumme: '.e(Number::currency((float) $record->subtotal, $currency, $locale))
                            .'<br>Gutschein '.e($record->discount_code)
                            .': −'.e(Number::currency($discount, $currency, $locale))
                        );
                    }),
                TextColumn::make('created_at')
                    ->label('Bestelldatum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
            ])
            ->recordActions([
                Action::make('mark_paid')
                    ->label('Bezahlt')
                    ->color('success')
                    ->icon('heroicon-m-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Bezahlung bestätigen')
                    // Der Hinweistext nennt die Zahl der Positionen, damit vor dem
                    // Klick klar ist, wie viele Artikel aus dem Shop gehen.
                    ->modalDescription(function (Order $record): string {
                        $count = $record->childrens->count();

                        $artikel = $count > 1
                            ? 'Die '.$count.' Artikel werden'
                            : 'Das Produkt wird';

                        return 'Möchtest du die Bestellung '.$record->orderNumber()
                            .' als bezahlt markieren? '.$artikel
                            .' jetzt aus dem Shop genommen, falls es Einzelstücke sind.'
                            .' Der Käufer bekommt eine Bestätigung für die ganze Bestellung.';
                    })
                    ->action(function (Order $record): void {
                        // Die ganze Bestellung in einem Schritt: Status und Bestand
                        // je Position, aber nur eine Bestätigung an den Käufer.
                        if (! $record->markOrderAsPaid()) {
                            Notification::make()
                                ->title('Bestellung war bereits als bezahlt markiert')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Bestellung '.$record->orderNumber().' als bezahlt markiert')
                            ->success()
                            ->send();
                    }),
                Action::make('cancelled')
                    ->label('Storniert')
                    ->color('gray')
                    ->disabled()
                    ->visible(fn (Order $record): bool => (int) ($record->status ?? 0) === 3),
            ])
            ->toolbarActions([]);
    }
}
