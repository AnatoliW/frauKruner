<?php

namespace App\Filament\Resources\Faqs\Tables;

use App\Faq;
use App\Filament\Resources\Faqs\FaqResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('question')
                    ->label('Frage')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('answer')
                    ->label('Antwort')
                    ->limit(160)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ((int) $state) {
                        1 => 'Verkäufer/in',
                        0 => 'Käufer/in',
                        default => 'Unbekannt',
                    })
                    ->color(fn ($state): string => (int) $state === 1 ? 'info' : 'gray')
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
                    DeleteAction::make()
                        ->label('Löschen'),
                    EditAction::make()
                        ->label('Bearbeiten'),
                    // Action::make('show')
                    //     ->label('Anzeigen')
                    //     ->icon('heroicon-m-eye')
                    //     ->color('warning')
                    //     ->url(fn (Faq $record): string => FaqResource::getUrl('edit', ['record' => $record])),
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
