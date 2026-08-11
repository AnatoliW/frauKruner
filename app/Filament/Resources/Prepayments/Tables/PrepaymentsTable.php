<?php

namespace App\Filament\Resources\Prepayments\Tables;

use App\Order;
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
                TextColumn::make('parent_id')
                    ->label('Haupt ID')
                    ->sortable()
                    ->searchable(),
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
                TextColumn::make('vendor.name')
                    ->label('Verkäuferin')
                    ->state(function (Order $record): string {
                        $name = trim(($record->vendor->name ?? '').' '.($record->vendor->last_name ?? ''));

                        return $name !== '' ? $name : '-';
                    })
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('vendor', function ($q) use ($search): void {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('product')
                    ->label('Produkt')
                    ->html()
                    ->formatStateUsing(function (Order $record): string {
                        if (!$record->product || !$record->product->slug) {
                            return '-';
                        }

                        $url = route('product', $record->product->slug);

                        return '<a href="'.e($url).'" target="_blank">'.e((string) $record->product->name).'</a>';
                    }),
                TextColumn::make('total')
                    ->label('Gesamt')
                    ->money()
                    ->sortable()
                    // 'total' ist der Bruttowert dieser Position; ein
                    // Gutscheinanteil geht davon noch ab. Gerade bei Vorkasse
                    // muss der Unterschied sichtbar sein – sonst wird hier auf
                    // einen Zahlungseingang gewartet, den es nie geben wird.
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

                        $paid = max(0, (float) $record->total - $discount);

                        return new HtmlString(
                            'Gutschein: '.e($record->discount_code)
                            .' −'.e(Number::currency($discount, $currency, $locale))
                            .'<br>Zu zahlen: '.e(Number::currency($paid, $currency, $locale))
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
                    ->modalDescription('Möchtest du diese Bestellung als bezahlt markieren? Das Produkt wird jetzt aus dem Shop genommen, falls es ein Einzelstück ist.')
                    ->action(function (Order $record): void {
                        if (!$record->markAsPaid()) {
                            Notification::make()
                                ->title('Bestellung war bereits als bezahlt markiert')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Bestellung als bezahlt markiert')
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
