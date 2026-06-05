<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\ProductResource;
use App\Product;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('user_id')
                    ->label('Nutzer Id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('details')
                    ->label('Details')
                    ->limit(120)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Beschreibung')
                    ->limit(120)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state): string => (int) $state === 1 ? 'Yes' : 'No')
                    ->sortable(),
                TextColumn::make('boosted')
                    ->label('Gepusht')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('boost_start_date')
                    ->label('Push-Start')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('boost_end_date')
                    ->label('Push-Ende')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('status')
                    ->label('Status')
                    ->trueLabel('Aktiv')
                    ->falseLabel('Inaktiv')
                    ->placeholder('Alle'),
                TernaryFilter::make('boosted')
                    ->label('Gepusht')
                    ->trueLabel('Ja')
                    ->falseLabel('Nein')
                    ->placeholder('Alle'),
                Filter::make('active_boost')
                    ->label('Aktiver Push')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('boosted', 1)
                        ->whereNotNull('boost_start_date')
                        ->whereNotNull('boost_end_date')
                        ->where('boost_start_date', '<=', now())
                        ->where('boost_end_date', '>=', now())),
                Filter::make('expired_boost')
                    ->label('Push abgelaufen')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('boosted', 1)
                        ->whereNotNull('boost_end_date')
                        ->where('boost_end_date', '<', now())),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('boost')
                        ->label('Produkt pushen')
                        ->icon('heroicon-m-arrow-up')
                        ->color('success')
                        ->url(fn (Product $record): string => ProductResource::getUrl('boost', ['record' => $record])),
                    DeleteAction::make()
                        ->label('Loeschen'),
                    // EditAction::make()
                    //     ->label('Bearbeiten'),
                ])
                    ->label('Aktionen')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
