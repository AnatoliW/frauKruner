<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('excerpt')
                    ->label('Auszug')
                    ->limit(120)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state): string => match (strtoupper((string) $state)) {
                        'PUBLISHED' => 'Veröffentlicht',
                        'DRAFT' => 'Entwurf',
                        'PENDING' => 'Ausstehend',
                        default => strtolower((string) $state),
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->date('d.m.Y')
                    ->sortable(),
                ImageColumn::make('image')
                    ->label('Beitragsbild')
                    ->disk('s3')
                    ->square(),
                TextColumn::make('category.name')
                    ->label('Kategorie')
                    ->formatStateUsing(fn ($state, Post $record): string => $state ?: ((string) ($record->category?->title ?? '-')))
                    ->wrap(),
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
                    //     ->url(fn (Post $record): string => PostResource::getUrl('edit', ['record' => $record])),
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
