<?php

namespace App\Filament\Resources\Finishings\Tables;

use App\Filament\Resources\Finishings\FinishingResource;
use App\Finishing;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinishingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    // Action::make('show')
                    //     ->label('Anzeigen')
                    //     ->icon('heroicon-m-eye')
                    //     ->color('warning')
                    //     ->url(fn (Finishing $record): string => FinishingResource::getUrl('view', ['record' => $record])),
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
