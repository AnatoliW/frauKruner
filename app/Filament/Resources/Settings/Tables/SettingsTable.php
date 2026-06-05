<?php

namespace App\Filament\Resources\Settings\Tables;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('group')
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->searchable(),
                TextColumn::make('display_name')
                    ->label('Titel')
                    ->searchable(),
                TextColumn::make('value')
                    ->label('Wert')
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Typ')
                    ->searchable(),
                TextColumn::make('group')
                    ->label('Gruppe')
                    ->badge()
                    ->searchable(),
                TextColumn::make('order')
                    ->label('Reihenfolge')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label('Gruppe')
                    ->options([
                        'Site' => 'Site',
                        'Admin' => 'Admin',
                        'code' => 'code',
                        'Finance' => 'Finance',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('show')
                        ->label('Anzeigen')
                        ->icon('heroicon-m-eye')
                        ->color('warning')
                        ->url(fn (Setting $record): string => SettingResource::getUrl('edit', ['record' => $record])),
                    EditAction::make()
                        ->label('Bearbeiten'),
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
