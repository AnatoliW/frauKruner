<?php

namespace App\Filament\Resources\Notifications\Tables;

use App\Filament\Resources\Notifications\NotificationResource;
use App\Notification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('role')
                    ->label('Nutzergruppe')
                    ->formatStateUsing(fn ($state): string => match ((int) $state) {
                        3 => 'Verkäufer',
                        2 => 'Käufer',
                        1 => 'Admin',
                        default => 'Alle',
                    })
                    ->sortable(),
                TextColumn::make('verified')
                    ->label('Verifizierung')
                    ->formatStateUsing(fn ($state): string => match ((int) $state) {
                        1 => 'Verifiziert',
                        2 => 'Unverifiziert',
                        default => 'Alle',
                    })
                    ->sortable(),
                TextColumn::make('user.username')
                    ->label('Nutzer')
                    ->formatStateUsing(fn ($state): string => $state ?: 'Keine Ergebnisse')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Titel')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Beschreibung')
                    ->formatStateUsing(function (?string $state): string {
                        $plain = html_entity_decode(strip_tags($state ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

                        return trim(preg_replace('/\s+/u', ' ', $plain) ?? '');
                    })
                    ->limit(140)
                    ->wrap(),
                TextColumn::make('is_email')
                    ->label('E-Mail versenden')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => (int) $state === 1 ? 'Ja' : 'Nein')
                    ->color(fn ($state): string => (int) $state === 1 ? 'info' : 'gray')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    DeleteAction::make()
                        ->label('Löschen'),
                    EditAction::make()
                        ->label('Bearbeiten'),
                    // Action::make('show')
                    //     ->label('Anzeigen')
                    //     ->icon('heroicon-m-eye')
                    //     ->color('warning')
                    //     ->url(fn (Notification $record): string => NotificationResource::getUrl('edit', ['record' => $record])),
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
