<?php

namespace App\Filament\Resources\Verifications;

use App\Filament\Resources\Verifications\Pages\CreateVerification;
use App\Filament\Resources\Verifications\Pages\EditVerification;
use App\Filament\Resources\Verifications\Pages\ListVerifications;
use App\Filament\Resources\Verifications\Schemas\VerificationForm;
use App\Filament\Resources\Verifications\Tables\VerificationsTable;
use App\Models\Verification;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VerificationResource extends BaseAdminResource
{
    protected static ?string $model = Verification::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ? string $navigationLabel = 'Verifizierungen';
    protected static string|\UnitEnum|null $navigationGroup = 'Nutzer';

    public static function form(Schema $schema): Schema
    {
        return VerificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VerificationsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user.role']);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVerifications::route('/'),
            'create' => CreateVerification::route('/create'),
            'edit' => EditVerification::route('/{record}/edit'),
        ];
    }
}

