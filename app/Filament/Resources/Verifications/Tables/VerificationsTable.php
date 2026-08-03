<?php

namespace App\Filament\Resources\Verifications\Tables;

use App\Models\Verification;
use App\Filament\Resources\Verifications\VerificationResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VerificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    // Vor- und Nachname anzeigen, Nutzername nur als Fallback.
                    ->formatStateUsing(fn ($state, Verification $record): string => trim(($record->user?->name ?? '') . ' ' . ($record->user?->last_name ?? ''))
                        ?: (trim($record->user?->username ?? '') ?: '-'))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas(
                        'user',
                        fn (Builder $userQuery): Builder => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%"),
                    )),
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
                TextColumn::make('user.verified')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => (int) $state === 1 ? 'Verifiziert' : 'Nicht verifiziert')
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
                    Action::make('view')
                        ->label('Ansehen')
                        ->icon('heroicon-m-eye')
                        ->color('warning')
                        ->url(fn (Verification $record): string => VerificationResource::getUrl('edit', ['record' => $record])),
                    EditAction::make()
                        ->label('Felder bearbeiten')
                        ->url(fn (Verification $record): string => VerificationResource::getUrl('edit-form', ['record' => $record])),
                    DeleteAction::make()
                        ->label('Löschen'),
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
