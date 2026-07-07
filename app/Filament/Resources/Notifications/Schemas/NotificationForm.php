<?php

namespace App\Filament\Resources\Notifications\Schemas;

use App\Models\User;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inhalt')
                    ->description('Titel und Text der Nachricht.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('Kurze Überschrift, die in der Nachrichtenliste angezeigt wird.'),
                        RichEditor::make('description')
                            ->label('Nachrichtentext')
                            ->required()
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('notifications/content')
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['h2', 'h3'],
                                ['alignStart', 'alignCenter', 'alignEnd'],
                                ['blockquote', 'bulletList', 'orderedList'],
                                ['undo', 'redo'],
                            ])
                            ->helperText('Formatierter Text mit visuellem Editor. Wird im Nutzerbereich als HTML angezeigt.'),
                    ]),

                Section::make('Empfänger')
                    ->description('Zielgruppe oder einzelner Nutzer festlegen.')
                    ->schema([
                        Select::make('role')
                            ->label('Nutzergruppe')
                            ->options([
                                3 => 'Verkäufer/in',
                                2 => 'Käufer/in',
                                1 => 'Administrator/in',
                            ])
                            ->nullable()
                            ->placeholder('Alle Nutzergruppen')
                            ->helperText('Leer lassen, wenn die Nachricht an alle Gruppen gehen soll.'),
                        Select::make('verified')
                            ->label('Verifizierungsstatus')
                            ->options([
                                0 => 'Alle',
                                1 => 'Nur verifizierte',
                                2 => 'Nur unverifizierte',
                            ])
                            ->default(0)
                            ->required()
                            ->helperText('Hinweis: Die Filterung nach Verifizierung ist im Nutzerbereich derzeit nicht aktiv.'),
                        Select::make('user_id')
                            ->label('Einzelner Nutzer')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'username',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->orderBy('username')
                                    ->orderBy('name'),
                            )
                            ->getOptionLabelFromRecordUsing(function (User $record): string {
                                $name = trim(collect([$record->username, $record->name, $record->last_name])->filter()->first() ?? '');

                                return $name !== ''
                                    ? "{$name} ({$record->email})"
                                    : (string) $record->email;
                            })
                            ->searchable(['username', 'name', 'last_name', 'email'])
                            ->preload()
                            ->nullable()
                            ->placeholder('Kein einzelner Nutzer')
                            ->helperText('Optional: Nachricht nur an einen bestimmten Nutzer senden.'),
                    ])
                    ->columns(2),

                Section::make('Versand')
                    ->description('Optionale E-Mail-Benachrichtigung.')
                    ->schema([
                        Toggle::make('is_email')
                            ->label('Als E-Mail versenden')
                            ->inline(false)
                            ->default(false)
                            ->onColor('info')
                            ->helperText('Wird im Frontend derzeit nicht für die Zustellung ausgewertet – nur gespeichert.')
                            ->afterStateHydrated(function (Toggle $component, $state): void {
                                $component->state((bool) ((int) $state));
                            })
                            ->dehydrateStateUsing(fn (bool $state): int => $state ? 1 : 0),
                    ]),
            ]);
    }
}
