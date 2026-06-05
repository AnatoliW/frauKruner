<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\OrderResource;
use App\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('parent_id')
                    ->label('Haupt ID')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user_id')
                    ->label('Nutzer ID')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Vorname')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Nachname')
                    ->searchable(),
                TextColumn::make('total')
                    ->label('Gesamt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vendor.name')
                    ->label('Verkaeuferin')
                    ->searchable(),
                TextColumn::make('payouts_status')
                    ->label('Status der Auszahlung')
                    ->formatStateUsing(fn ($state): string => (int) $state === 1 ? 'Ausgezahlt' : 'Nicht ausgezahlt')
                    ->badge()
                    ->color(fn ($state): string => (int) $state === 1 ? 'success' : 'primary')
                    ->sortable(),
                TextColumn::make('vendor_total')
                    ->label('Verkaeuferin bekommt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('commission')
                    ->label('Komission')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('shipping_date')
                    ->label('Versanddatum')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('payment_id')
                    ->label('Zahlungs-ID')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Bestelldatum')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('Ansehen')
                        ->color('warning')
                        ->icon('heroicon-m-eye')
                        ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
                    Action::make('cancel')
                        ->label('Stornieren')
                        ->color('danger')
                        ->icon('heroicon-m-x-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Bestellung stornieren')
                        ->modalDescription('Bist du sicher, dass du die Bestellung stornieren moechtest?')
                        ->url(fn (Order $record): string => route('admin.order.cancel', $record))
                        ->visible(fn (Order $record): bool => (int) ($record->status ?? 0) !== 3),
                    Action::make('cancelled')
                        ->label('Storniert')
                        ->color('gray')
                        ->disabled()
                        ->visible(fn (Order $record): bool => (int) ($record->status ?? 0) === 3),
                ])
                    ->label('Aktionen')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
