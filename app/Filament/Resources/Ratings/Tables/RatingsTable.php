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
            ->recordUrl(null)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->date('d.m.Y')
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
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search): void {
                            $q->where('ratings.email', 'like', "%{$search}%")
                                ->orWhere('ratings.name', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($q) use ($search): void {
                                    $q->where('email', 'like', "%{$search}%")
                                        ->orWhere('name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhereRaw("CONCAT_WS(' ', name, last_name) LIKE ?", ["%{$search}%"])
                                        ->orWhere('username', 'like', "%{$search}%");
                                });
                        });
                    }),
                TextColumn::make('vendor.email')
                    ->label('Verkäufer')
                    ->formatStateUsing(fn ($state): string => $state ?: '-')
                    ->sortable()
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('vendor', function ($q) use ($search): void {
                            $q->where('email', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhereRaw("CONCAT_WS(' ', name, last_name) LIKE ?", ["%{$search}%"])
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                    }),
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
