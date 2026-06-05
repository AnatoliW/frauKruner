<?php

namespace App\Filament\Resources\Boosts\Tables;

use App\Filament\Resources\Boosts\BoostResource;
use App\Models\Boost;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BoostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(15)
            ->paginationPageOptions([15, 25, 50])
            ->columns([
                TextColumn::make('boostable_label')
                    ->label('Was wurde gepusht')
                    ->getStateUsing(function (Boost $record): string {
                        $name = $record->boostable?->name ?? $record->boostable?->title ?? '-';
                        $type = class_basename((string) $record->boostable_type) === 'User' ? 'Seller' : 'Product';

                        return $name . ' (' . $type . ')';
                    })
                    ->wrap(),
                TextColumn::make('package.name')
                    ->label('Push-Option')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Preis')
                    ->money()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => (int) $state === 1 ? 'Aktiv' : 'Inaktiv')
                    ->color(fn ($state): string => (int) $state === 1 ? 'info' : 'gray')
                    ->sortable(),
                TextColumn::make('tax')
                    ->label('Tax')
                    ->money()
                    ->sortable(),
                TextColumn::make('start_day')
                    ->label('Start')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('end_day')
                    ->label('Ende')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('user_full_name')
                    ->label('Nutzer')
                    ->getStateUsing(fn (Boost $record): string => trim(($record->user?->name ?? '') . ' ' . ($record->user?->last_name ?? ''))),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('invoice')
                    ->label('Rechnung')
                    ->color('info')
                    ->url(fn (Boost $record): string => BoostResource::getUrl('invoice', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
