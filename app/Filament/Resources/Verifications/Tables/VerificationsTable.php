<?php

namespace App\Filament\Resources\Verifications\Tables;

use App\Models\Verification;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VerificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.username')
                    ->label('Name')
                    ->formatStateUsing(fn ($state, Verification $record): string => $state ?: ($record->user?->name ?? '-'))
                    ->searchable(),
                TextColumn::make('user.role.display_name')
                    ->label('Rolle')
                    ->formatStateUsing(fn ($state, Verification $record): string => $state ?: ((int) ($record->user?->role_id ?? 0) === 3 ? 'Verkäufer/in' : ((int) ($record->user?->role_id ?? 0) === 2 ? 'Käufer/in' : 'Administrator')))
                    ->sortable(),
                TextColumn::make('street')
                    ->label('Straße')
                    ->searchable(),
                TextColumn::make('house_no')
                    ->label('HausNr.')
                    ->searchable(),
                TextColumn::make('city')
                    ->label('Stadt')
                    ->searchable(),
                TextColumn::make('zip')
                    ->label('PLZ')
                    ->searchable(),
                TextColumn::make('date_of_birth')
                    ->label('Geburtsdatum')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => (int) $state === 1 ? 'Verifiziert' : 'Ausstehend')
                    ->color(fn ($state): string => (int) $state === 1 ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Bearbeiten'),
                    DeleteAction::make()
                        ->label('Loeschen'),
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
