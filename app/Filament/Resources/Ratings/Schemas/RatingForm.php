<?php

namespace App\Filament\Resources\Ratings\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RatingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bewertung')
                    ->description('Sterne und Bewertungstext.')
                    ->schema([
                        Select::make('rating')
                            ->label('Sterne')
                            ->options([
                                '5' => '5 Sterne – Sehr gut',
                                '4.5' => '4,5 Sterne – Sehr gut',
                                '4' => '4 Sterne – Gut',
                                '3.5' => '3,5 Sterne – Gut',
                                '3' => '3 Sterne – Befriedigend',
                                '2.5' => '2,5 Sterne – Ausreichend',
                                '2' => '2 Sterne – Ausreichend',
                                '1.5' => '1,5 Sterne – Mangelhaft',
                                '1' => '1 Stern – Mangelhaft',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable(),
                        Textarea::make('review')
                            ->label('Bewertungstext')
                            ->rows(5)
                            ->columnSpanFull()
                            ->maxLength(65535)
                            ->helperText('Der Text, der im Profil des Verkäufers angezeigt wird.'),
                    ])
                    ->columns(2),

                Section::make('Beteiligte')
                    ->description('Käufer/in bewertet Verkäufer/in-Profil.')
                    ->schema([
                        Select::make('user_id')
                            ->label('Bewertender')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'username',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->orderBy('username')
                                    ->orderBy('name'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (User $record): string => self::formatUserLabel($record))
                            ->searchable(['username', 'name', 'last_name', 'email'])
                            ->preload()
                            ->nullable()
                            ->placeholder('Kein verknüpfter Nutzer')
                            ->helperText('Das Nutzerkonto, von dem die Bewertung stammt (in der Regel ein/e Käufer/in).'),
                        Select::make('vendor_id')
                            ->label('Bewerteter Verkäufer/in')
                            ->relationship(
                                name: 'vendor',
                                titleAttribute: 'username',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('role_id', 3)
                                    ->orderBy('username')
                                    ->orderBy('name'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (User $record): string => self::formatUserLabel($record))
                            ->searchable(['username', 'name', 'last_name', 'email'])
                            ->preload()
                            ->required()
                            ->helperText('Der Verkäufer, dessen Profil bewertet wurde.'),
                    ])
                    ->columns(2),

                Section::make('Gastdaten')
                    ->description('Nur relevant, wenn die Bewertung ohne Nutzerkonto abgegeben wurde.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Anzeigename')
                            ->maxLength(255)
                            ->helperText('Wird angezeigt, wenn kein Nutzerkonto verknüpft ist.'),
                        TextInput::make('email')
                            ->label('E-Mail-Adresse')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Kontakt-E-Mail bei Gastbewertungen.'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(fn (Get $get): bool => filled($get('user_id'))),
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
