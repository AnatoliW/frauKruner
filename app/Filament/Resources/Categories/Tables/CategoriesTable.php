<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Category;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')
                    ->label('Reihenfolge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('featured')
                    ->label('Auf der Startseite?')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => (int) $state === 1 ? 'Ja' : 'Nein')
                    ->color(fn ($state): string => (int) $state === 1 ? 'success' : 'gray')
                    ->sortable(),
                ImageColumn::make('image')
                    ->label('Bild')
                    ->disk('s3'),
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Beschreibung')
                    ->limit(80)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('color')
                    ->label('Hintergrundfarbe')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('font')
                    ->label('Schriftfarbe')
                    ->badge()
                    ->color('gray'),
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
                    //     ->url(fn (Category $record): string => CategoryResource::getUrl('view', ['record' => $record])),
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
