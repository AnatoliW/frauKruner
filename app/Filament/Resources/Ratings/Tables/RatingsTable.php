<?php

namespace App\Filament\Resources\Ratings\Tables;

use App\Filament\Resources\Ratings\RatingResource;
use App\Rating;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RatingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Sterne')
                    ->sortable(),
                TextColumn::make('review')
                    ->label('Bewertung')
                    ->limit(180)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Nutzer')
                    ->formatStateUsing(fn ($state, Rating $record): string => $state ?: ($record->email ?? '-'))
                    ->searchable(),
                TextColumn::make('vendor.email')
                    ->label('Verkäufer')
                    ->formatStateUsing(fn ($state): string => $state ?: '-')
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
                    //     ->url(fn (Rating $record): string => RatingResource::getUrl('edit', ['record' => $record])),
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
