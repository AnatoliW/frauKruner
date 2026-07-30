<?php

namespace App\Filament\Resources\Prepayments\Tables;

use App\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrepaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('parent_id')
                    ->label('Haupt ID')
                    ->sortable(),
                TextColumn::make('user_id')
                    ->label('Nutzer ID')
                    ->sortable(),
                TextColumn::make('buyer')
                    ->label('Käufer')
                    ->html()
                    ->state(function (Order $record): string {
                        $name = trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? ''));
                        $email = trim((string) ($record->email ?? ''));

                        if ($email === '') {
                            return e($name);
                        }

                        return e($name) . '<br><a href="mailto:' . e($email) . '">' . e($email) . '</a>';
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
                    ->searchable(),
                TextColumn::make('product')
                    ->label('Produkt')
                    ->html()
                    ->formatStateUsing(function (Order $record): string {
                        if (! $record->product || ! $record->product->slug) {
                            return '-';
                        }

                        $url = route('product', $record->product->slug);

                        return '<a href="' . e($url) . '" target="_blank">' . e((string) $record->product->name) . '</a>';
                    }),
                TextColumn::make('total')
                    ->label('Gesamt')
                    ->money()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Bestelldatum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
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
                        if (! $record->markAsPaid()) {
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
