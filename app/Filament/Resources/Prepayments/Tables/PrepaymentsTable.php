<?php

namespace App\Filament\Resources\Prepayments\Tables;

use App\Mail\VendorOrderEmail;
use App\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class PrepaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('parent_id')
                    ->label('Haupt ID')
                    ->sortable(),
                TextColumn::make('user_id')
                    ->label('Nutzer ID')
                    ->sortable(),
                TextColumn::make('buyer')
                    ->label('Käufer')
                    ->html()
                    ->formatStateUsing(function (Order $record): string {
                        $name = trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? ''));
                        $email = (string) ($record->email ?? '');

                        if ($email === '') {
                            return e($name);
                        }

                        return e($name) . '<br><a href="mailto:' . e($email) . '">' . e($email) . '</a>';
                    })
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search): void {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('vendor.name')
                    ->label('Verkäuferin')
                    ->searchable(),
                TextColumn::make('product')
                    ->label('Produkt')
                    ->html()
                    ->formatStateUsing(function (Order $record): string {
                        if (! $record->product || ! $record->product->slug) {
                            return '-';
                        }

                        $url = route('product', $record->product->slug);

                        return '<a href="' . e($url) . '" target="_blank">' . e((string) $record->product->name) . '</a>';
                    }),
                TextColumn::make('total')
                    ->label('Gesamt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Bestelldatum')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('mark_paid')
                    ->label('Bezahlt')
                    ->color('success')
                    ->icon('heroicon-m-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Bezahlung bestaetigen')
                    ->modalDescription('Moechtest du diese Bestellung als bezahlt markieren?')
                    ->action(function (Order $record): void {
                        $record->update([
                            'payment_status' => 1,
                            'status' => 1,
                        ]);

                        if ($record->parent) {
                            $record->parent->update([
                                'payment_status' => 1,
                                'status' => 1,
                            ]);
                        }

                        Mail::to($record->vendor->email)->send(new VendorOrderEmail($record));

                        Notification::make()
                            ->title('Bestellung als bezahlt markiert')
                            ->success()
                            ->send();
                    }),
                Action::make('cancelled')
                    ->label('Storniert')
                    ->color('gray')
                    ->disabled()
                    ->visible(fn (Order $record): bool => (int) ($record->status ?? 0) === 3),
            ])
            ->toolbarActions([]);
    }
}
