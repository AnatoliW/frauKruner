<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Beitrag schreiben')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set(
                                'slug',
                                Str::slug($state ?? ''),
                            )),
                        Textarea::make('excerpt')
                            ->label('Auszug (Teaser für die Übersicht)')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Kurzer Text unter dem Titel auf /Neuigkeiten – optional, aber empfohlen.'),
                        RichEditor::make('body')
                            ->label('Beitragstext')
                            ->required()
                            ->columnSpanFull()
                            ->fileAttachments(true)
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('posts/content')
                            ->fileAttachmentsVisibility('public')
                            ->fileAttachmentsAcceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/webp',
                                'image/heic',
                                'image/heif',
                                'image/avif',
                            ])
                            ->fileAttachmentsMaxSize(20480)
                            ->resizableImages()
                            ->toolbarButtons([
                                ['attachFiles'],
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['h2', 'h3'],
                                ['alignStart', 'alignCenter', 'alignEnd'],
                                ['blockquote', 'bulletList', 'orderedList'],
                                ['undo', 'redo'],
                            ])
                            ->helperText('Fotos einfügen: Button „Dateien anhängen“ in der Toolbar, per Drag & Drop oder mit Strg+V in den Text.')
                            ->extraInputAttributes([
                                'style' => 'min-height: 60vh; font-size: 1.125rem; line-height: 1.75;',
                            ]),
                    ])
                    ->extraAttributes([
                        'class' => 'fi-post-writing-section',
                    ]),

                Section::make('Veröffentlichung')
                    ->schema([
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Wird automatisch aus dem Titel erzeugt. URL: /Neuigkeiten/{slug}'),
                        TextInput::make('seo_title')
                            ->label('SEO-Titel')
                            ->maxLength(255)
                            ->helperText('Optional. Überschreibt den Titel in Suchmaschinen.'),
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

                Section::make('SEO & Medien')
                    ->description('Meta-Angaben und Beitragsbild für die Übersicht.')
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
                            ->disk('s3')
                            ->directory('posts')
                            ->columnSpanFull()
                            ->helperText('Vorschaubild in der Beitragsliste und oben auf der Beitragsseite.'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
