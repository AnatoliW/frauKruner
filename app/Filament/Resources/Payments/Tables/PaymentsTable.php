<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payable_id')
                    ->label('Verknüpfte ID')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payable_type')
                    ->label('Verknüpfter Typ')
                    ->searchable(),
                TextColumn::make('payment_trnx_id')
                    ->label('Transaktions-ID')
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label('Zahlungsmethode')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Betrag')
                    ->money()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert am')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tax')
                    ->label('MwSt.')
                    ->money()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Bearbeiten'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Löschen'),
                ]),
            ]);
    }
}
