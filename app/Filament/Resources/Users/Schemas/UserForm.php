<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('role_id')
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('last_name'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('avatar')
                    ->default('users/default.png'),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Textarea::make('two_factor_secret')
                    ->columnSpanFull(),
                Textarea::make('two_factor_recovery_codes')
                    ->columnSpanFull(),
                Textarea::make('settings')
                    ->columnSpanFull(),
                TextInput::make('username'),
                TextInput::make('status')
                    ->numeric(),
                TextInput::make('visibiliti_status')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('commission')
                    ->numeric(),
                TextInput::make('verified')
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('verification_deleted_at'),
                TextInput::make('resend')
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('last_login_at')
                    ->required(),
                DateTimePicker::make('email_send_at'),
                TextInput::make('boosted')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('boost_start_date'),
                DateTimePicker::make('boost_end_date'),
                Toggle::make('is_commercial')
                    ->required(),
            ]);
    }
}
