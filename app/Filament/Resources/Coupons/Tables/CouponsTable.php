<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Coupon;
use App\Filament\Resources\Coupons\CouponResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable(),
                TextColumn::make('discount')
                    ->label('Rabatt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expire_at')
                    ->label('Läuft ab am')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('limit')
                    ->label('Limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('minimum_cart')
                    ->label('Minimaler Warenkorbwert')
                    ->money()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('used')
                    ->label('Genutzt')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Bearbeiten'),
                    DeleteAction::make()
                        ->label('Löschen'),
                    // Action::make('show')
                    //     ->label('Anzeigen')
                    //     ->icon('heroicon-m-eye')
                    //     ->color('warning')
                    //     ->url(fn (Coupon $record): string => CouponResource::getUrl('edit', ['record' => $record])),
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
