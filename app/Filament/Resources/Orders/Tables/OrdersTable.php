<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\OrderResource;
use App\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('parent_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vendor_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->searchable(),
                TextColumn::make('additional')
                    ->searchable(),
                TextColumn::make('street')
                    ->searchable(),
                TextColumn::make('house_no')
                    ->searchable(),
                TextColumn::make('zip')
                    ->searchable(),
                TextColumn::make('federal_state')
                    ->searchable(),
                TextColumn::make('po_box')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_code')
                    ->searchable(),
                TextColumn::make('subtotal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('shipping_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('shipping_method')
                    ->searchable(),
                TextColumn::make('tracking_Id')
                    ->searchable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_gateway')
                    ->searchable(),
                TextColumn::make('shipped')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('video_uploaded_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payouts_status')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payouts_rerquest')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vendor_total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('commission')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('photo')
                    ->searchable(),
                TextColumn::make('video')
                    ->searchable(),
                TextColumn::make('shipping_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('send_shipping_email')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_id')
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('is_rated')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product_name')
                    ->searchable(),
                IconColumn::make('meta_remove_status')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Ansehen')
                    ->color('warning')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
                Action::make('payout')
                    ->label('Auszahlen')
                    ->color('success')
                    ->icon('heroicon-m-wallet')
                    ->requiresConfirmation()
                    ->modalHeading('Auszahlung bestaetigen')
                    ->modalDescription('Bist du sicher, dass du die Bestellung als ausgezahlt markieren moechtest?')
                    ->url(fn (Order $record): string => route('payout', [$record, request()->integer('page', 1)]))
                    ->visible(fn (Order $record): bool => (int) ($record->payouts_status ?? 0) === 0),
                Action::make('cancel')
                    ->label('Stornieren')
                    ->color('danger')
                    ->icon('heroicon-m-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Bestellung stornieren')
                    ->modalDescription('Bist du sicher, dass du die Bestellung stornieren moechtest?')
                    ->url(fn (Order $record): string => route('admin.order.cancel', $record))
                    ->visible(fn (Order $record): bool => (int) ($record->status ?? 0) !== 3),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
