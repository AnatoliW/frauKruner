<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\OrderResource;
use App\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                    ->money()
                    ->sortable(),
                TextColumn::make('vendor.name')
                    ->label('Verkaeuferin')
                    ->searchable(),
                TextColumn::make('payouts_status')
                    ->label(new HtmlString('Status der<br>Auszahlung'))
                    ->wrapHeader()
                    ->formatStateUsing(fn ($state): string => (int) $state === 1 ? 'Ausgezahlt' : 'Nicht ausgezahlt')
                    ->badge()
                    ->color(fn ($state): string => (int) $state === 1 ? 'success' : 'primary')
                    ->sortable(),
                TextColumn::make('vendor_total')
                    ->label(new HtmlString('Verkaeuferin<br>bekommt'))
                    ->wrapHeader()
                    ->money()
                    ->sortable(),
                TextColumn::make('commission')
                    ->label('Komision')
                    ->money()
                    ->sortable(),
                TextColumn::make('shipping_date')
                    ->label('Versanddatum')
                    ->date('d.m.Y')
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
                Action::make('view')
                    ->label('Ansehen')
                    ->button()
                    ->size('sm')
                    ->color('warning')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
                Action::make('cancel')
                    ->label('Stornieren')
                    ->button()
                    ->size('sm')
                    ->color('danger')
                    ->icon('heroicon-m-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Bestellung stornieren')
                    ->modalDescription('Bist du sicher, dass du die Bestellung stornieren Möchtest?')
                    ->url(fn (Order $record): string => route('admin.order.cancel', $record))
                    ->visible(fn (Order $record): bool => (int) ($record->status ?? 0) !== 3),
                Action::make('cancelled')
                    ->label('Storniert')
                    ->button()
                    ->size('sm')
                    ->color('gray')
                    ->disabled()
                    ->visible(fn (Order $record): bool => (int) ($record->status ?? 0) === 3),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
