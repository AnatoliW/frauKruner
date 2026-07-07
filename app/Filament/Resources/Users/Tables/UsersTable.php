<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('username')
                    ->label('Nutzername')
                    ->searchable(),
                ImageColumn::make('avatar')
                    ->label('Profilbild')
                    ->square()
                    ->disk('public')
                    ->size(88)
                    ->defaultImageUrl(asset('images/avatar/04.png')),
                TextColumn::make('profile.description')
                    ->label('Profilbeschreibung')
                    ->formatStateUsing(function (?string $state): string {
                        $plain = html_entity_decode(strip_tags($state ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

                        return trim(preg_replace('/\s+/u', ' ', $plain) ?? '');
                    })
                    ->limit(180)
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('profile', function (Builder $query) use ($search): void {
                            $query->where('description', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('name')
                    ->label('Vorname')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Nachname')
                    ->searchable(),
                TextColumn::make('role.display_name')
                    ->label('Nutzerkategorie')
                    ->formatStateUsing(fn ($state, User $record): string => $state ?: ((int) $record->role_id === 3 ? 'Verkäufer/in' : ((int) $record->role_id === 2 ? 'Käufer/in' : 'Administrator')))
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable(),
                TextColumn::make('boosted')
                    ->label('Gepusht')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => (int) $state === 1 ? 'Gepusht' : 'Pausiert')
                    ->color(fn ($state): string => (int) $state === 1 ? 'success' : 'info')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => (int) $state === 1 ? 'Aktiv' : 'Pausiert')
                    ->color(fn ($state): string => (int) $state === 1 ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('verified')
                    ->label('Verifiziert')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => (int) $state === 1 ? 'Verifiziert' : 'Unverifiziert')
                    ->color(fn ($state): string => (int) $state === 1 ? 'success' : 'info')
                    ->sortable(),
                IconColumn::make('is_commercial')
                    ->label('Gewerblich')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('boosted')
                    ->label('Gepusht')
                    ->trueLabel('Ja')
                    ->falseLabel('Nein')
                    ->placeholder('Alle'),
                TernaryFilter::make('status')
                    ->label('Status')
                    ->trueLabel('Aktiv')
                    ->falseLabel('Pausiert')
                    ->placeholder('Alle'),
                TernaryFilter::make('verified')
                    ->label('Verifiziert')
                    ->trueLabel('Ja')
                    ->falseLabel('Nein')
                    ->placeholder('Alle'),
                Filter::make('incomplete')
                    ->label('Unvollständige Verkäufer/innen')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('role_id', 3)
                        ->whereNull('verification_deleted_at')
                        ->doesntHave('verification')
                        ->where(function (Builder $query): void {
                            $query->whereNull('status')->orWhere('status', 0);
                        })),
            ])
            ->recordActions([
                ActionGroup::make([
                    DeleteAction::make()
                        ->label('Loeschen'),
                    EditAction::make()
                        ->label('Bearbeiten'),
                    Action::make('boost_profile')
                        ->label('Profil pushen')
                        ->icon('heroicon-m-arrow-up')
                        ->color('success')
                        ->url(fn (User $record): string => UserResource::getUrl('boost', ['record' => $record])),
                    // Action::make('show')
                    //     ->label('Anzeigen')
                    //     ->icon('heroicon-m-eye')
                    //     ->color('warning')
                    //     ->url(fn (User $record): string => UserResource::getUrl('view', ['record' => $record])),
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
