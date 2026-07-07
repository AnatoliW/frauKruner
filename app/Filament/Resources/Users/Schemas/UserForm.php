<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grunddaten')
                    ->description('Name, Login und Rolle des Nutzers.')
                    ->schema([
                        TextInput::make('username')
                            ->label('Nutzername')
                            ->maxLength(255),
                        TextInput::make('name')
                            ->label('Vorname')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label('Nachname')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-Mail-Adresse')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Select::make('role_id')
                            ->label('Nutzerkategorie')
                            ->options([
                                1 => 'Administrator',
                                2 => 'Käufer/in',
                                3 => 'Verkäufer/in',
                            ])
                            ->searchable()
                            ->required(),
                        TextInput::make('avatar')
                            ->label('Avatar-Pfad')
                            ->default('users/default.png')
                            ->helperText('Relativer Pfad zum Profilbild, z. B. users/default.png'),
                        TextInput::make('password')
                            ->label('Passwort')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText('Beim Bearbeiten leer lassen, wenn das Passwort unverändert bleiben soll.'),
                    ])
                    ->columns(2),

                Section::make('Status & Verifizierung')
                    ->description('Sichtbarkeit, Aktivstatus und Verifizierung.')
                    ->schema([
                        Toggle::make('status')
                            ->label('Aktiv')
                            ->inline(false)
                            ->onColor('success')
                            ->afterStateHydrated(function (Toggle $component, $state): void {
                                $component->state((bool) ((int) $state));
                            })
                            ->dehydrateStateUsing(fn (bool $state): int => $state ? 1 : 0),
                        Toggle::make('visibiliti_status')
                            ->label('Sichtbar')
                            ->inline(false)
                            ->default(true)
                            ->afterStateHydrated(function (Toggle $component, $state): void {
                                $component->state((bool) ((int) ($state ?? 1)));
                            })
                            ->dehydrateStateUsing(fn (bool $state): int => $state ? 1 : 0),
                        Toggle::make('verified')
                            ->label('Verifiziert')
                            ->inline(false)
                            ->afterStateHydrated(function (Toggle $component, $state): void {
                                $component->state((bool) ((int) $state));
                            })
                            ->dehydrateStateUsing(fn (bool $state): int => $state ? 1 : 0),
                        Toggle::make('is_commercial')
                            ->label('Gewerblich')
                            ->inline(false)
                            ->afterStateHydrated(function (Toggle $component, $state): void {
                                $component->state((bool) ((int) $state));
                            })
                            ->dehydrateStateUsing(fn (bool $state): int => $state ? 1 : 0),
                        TextInput::make('resend')
                            ->label('Verifizierung erneut senden (Zähler)')
                            ->numeric()
                            ->default(0),
                        DateTimePicker::make('verification_deleted_at')
                            ->label('Verifizierung gelöscht am')
                            ->seconds(false),
                        DateTimePicker::make('email_verified_at')
                            ->label('E-Mail verifiziert am')
                            ->seconds(false),
                    ])
                    ->columns(2),

                Section::make('Push & Finanzen')
                    ->description('Profil-Push und Provision.')
                    ->schema([
                        Toggle::make('boosted')
                            ->label('Gepusht')
                            ->inline(false)
                            ->afterStateHydrated(function (Toggle $component, $state): void {
                                $component->state((bool) ((int) $state));
                            })
                            ->dehydrateStateUsing(fn (bool $state): int => $state ? 1 : 0),
                        DateTimePicker::make('boost_start_date')
                            ->label('Push-Start')
                            ->seconds(false),
                        DateTimePicker::make('boost_end_date')
                            ->label('Push-Ende')
                            ->seconds(false),
                        TextInput::make('commission')
                            ->label('Kommission')
                            ->numeric()
                            ->suffix('%'),
                    ])
                    ->columns(2),

                Section::make('Konto')
                    ->description('Login- und Systemzeiten.')
                    ->schema([
                        DateTimePicker::make('last_login_at')
                            ->label('Letzter Login')
                            ->seconds(false),
                        DateTimePicker::make('email_send_at')
                            ->label('Letzte E-Mail gesendet am')
                            ->seconds(false),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
