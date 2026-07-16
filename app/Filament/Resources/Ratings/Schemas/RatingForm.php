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
                    ->schema([
                        Select::make('rating')
                            ->label('Sterne')
                            ->options([
                                '5' => '5 Sterne',
                                '4.5' => '4,5 Sterne',
                                '4' => '4 Sterne',
                                '3.5' => '3,5 Sterne',
                                '3' => '3 Sterne',
                                '2.5' => '2,5 Sterne',
                                '2' => '2 Sterne',
                                '1.5' => '1,5 Sterne',
                                '1' => '1 Stern',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable(),
                        Textarea::make('review')
                            ->label('Bewertungstext')
                            ->rows(5)
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ])
                    ->columns(2),

                Section::make('Beteiligte')
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
                            ->placeholder('Kein verknüpfter Nutzer'),
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
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Gastdaten')
                    ->schema([
                        TextInput::make('name')
                            ->label('Anzeigename')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-Mail-Adresse')
                            ->email()
                            ->maxLength(255),
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
