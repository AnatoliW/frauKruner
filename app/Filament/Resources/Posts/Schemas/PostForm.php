<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grunddaten')
                    ->description('Titel, URL, Kategorie und Veröffentlichungsstatus.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('seo_title')
                            ->label('SEO-Titel')
                            ->maxLength(255)
                            ->helperText('Optional. Überschreibt den Titel in Suchmaschinen, falls gesetzt.'),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL unter /Neuigkeiten/{slug}'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'PUBLISHED' => 'Veröffentlicht',
                                'DRAFT' => 'Entwurf',
                                'PENDING' => 'Ausstehend',
                            ])
                            ->default('DRAFT')
                            ->required()
                            ->native(false),
                        Select::make('category_id')
                            ->label('Kategorie')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Keine Kategorie'),
                    ])
                    ->columns(2),

                Section::make('Inhalt')
                    ->description('Teaser und Beitragstext.')
                    ->schema([
                        Textarea::make('excerpt')
                            ->label('Auszug')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Kurzer Teaser für Übersichtsseiten.'),
                        Textarea::make('short_description')
                            ->label('Kurzbeschreibung')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Wird in der Admin-Übersicht angezeigt. Falls leer, wird der Auszug genutzt.'),
                        RichEditor::make('body')
                            ->label('Beitragstext')
                            ->required()
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('posts/content')
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['h2', 'h3'],
                                ['alignStart', 'alignCenter', 'alignEnd'],
                                ['blockquote', 'bulletList', 'orderedList'],
                                ['undo', 'redo'],
                            ])
                            ->helperText('Formatierter Beitragstext. Wird auf der Webseite als HTML angezeigt.'),
                    ]),

                Section::make('SEO & Medien')
                    ->description('Meta-Angaben und Beitragsbild.')
                    ->schema([
                        Textarea::make('meta_description')
                            ->label('Meta-Beschreibung')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('meta_keywords')
                            ->label('Meta-Schlüsselwörter')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Kommagetrennte Keywords.'),
                        FileUpload::make('image')
                            ->label('Beitragsbild')
                            ->image()
                            ->directory('posts')
                            ->columnSpanFull()
                            ->helperText('Wird oben auf der Beitragsseite angezeigt.'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
