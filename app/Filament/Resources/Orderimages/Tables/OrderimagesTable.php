<?php

namespace App\Filament\Resources\Orderimages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderimagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('image')
                    ->disk('s3'),
                TextColumn::make('created_at')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('meta_remove_status')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
