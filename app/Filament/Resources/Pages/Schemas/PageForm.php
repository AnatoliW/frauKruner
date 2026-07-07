<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grunddaten')
                    ->description('Titel, URL und Veröffentlichungsstatus.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('Seitentitel für Browser-Tab und interne Zuordnung.'),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-Pfad unter /page/{slug}, z. B. datenschutz oder safe-zone.'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'ACTIVE' => 'Aktiv (öffentlich sichtbar)',
                                'INACTIVE' => 'Inaktiv (nicht erreichbar)',
                            ])
                            ->default('ACTIVE')
                            ->required()
                            ->native(false),
                        Select::make('author_id')
                            ->label('Autor/in')
                            ->relationship(
                                name: 'author',
                                titleAttribute: 'username',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->orderBy('username')
                                    ->orderBy('name'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (User $record): string => self::formatUserLabel($record))
                            ->searchable(['username', 'name', 'last_name', 'email'])
                            ->preload()
                            ->nullable()
                            ->placeholder('Kein Autor hinterlegt')
                            ->helperText('Optional. Wird im Frontend derzeit nicht angezeigt.'),
                    ])
                    ->columns(2),

                Section::make('Inhalt')
                    ->description('Seiteninhalt als HTML – wird unverändert auf der Webseite ausgegeben.')
                    ->schema([
                        Textarea::make('excerpt')
                            ->label('Kurztext')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Optionaler Teaser. Wird auf den meisten Seiten derzeit nicht genutzt.'),
                        Textarea::make('body')
                            ->label('Seiteninhalt (HTML)')
                            ->required()
                            ->rows(24)
                            ->columnSpanFull()
                            ->extraAttributes(['style' => 'font-family: ui-monospace, monospace; font-size: 13px;'])
                            ->helperText('Vollständiger HTML-Inhalt der Seite. Bestehendes Markup bitte beim Bearbeiten erhalten.'),
                    ]),

                Section::make('SEO & Medien')
                    ->description('Meta-Angaben und optionales Beitragsbild.')
                    ->schema([
                        Textarea::make('meta_description')
                            ->label('Meta-Beschreibung')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Kurzbeschreibung für Suchmaschinen (meta description).'),
                        Textarea::make('meta_keywords')
                            ->label('Meta-Schlüsselwörter')
                            ->rows(2)
                            ->maxLength(65535)
                            ->helperText('Kommagetrennte Keywords (meta keywords).'),
                        FileUpload::make('image')
                            ->label('Beitragsbild')
                            ->image()
                            ->directory('pages')
                            ->columnSpanFull()
                            ->helperText('Optional. Wird auf den Standard-Seitenlayouts derzeit nicht ausgegeben.'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    private static function formatUserLabel(User $record): string
    {
        $name = trim(collect([$record->username, $record->name, $record->last_name])->filter()->first() ?? '');

        return $name !== ''
            ? "{$name} ({$record->email})"
            : (string) $record->email;
    }
}
