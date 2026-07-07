<?php

namespace App\Filament\Resources\PaymentIcons;

use App\Filament\Resources\PaymentIcons\Pages\CreatePaymentIcon;
use App\Filament\Resources\PaymentIcons\Pages\EditPaymentIcon;
use App\Filament\Resources\PaymentIcons\Pages\ListPaymentIcons;
use App\Filament\Resources\PaymentIcons\Schemas\PaymentIconForm;
use App\Filament\Resources\PaymentIcons\Tables\PaymentIconsTable;
use App\PaymentIcon;
use BackedEnum;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentIconResource extends BaseAdminResource
{
    protected static ?string $model = PaymentIcon::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ? string $navigationLabel = 'Zahlungsmethoden';

    protected static ?string $modelLabel = 'Zahlungsmethode';

    protected static ?string $pluralModelLabel = 'Zahlungsmethoden';
    protected static string|\UnitEnum|null $navigationGroup = 'Einstellungen';

    public static function form(Schema $schema): Schema
    {
        return PaymentIconForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentIconsTable::configure($table);
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
            'index' => ListPaymentIcons::route('/'),
            'create' => CreatePaymentIcon::route('/create'),
            'edit' => EditPaymentIcon::route('/{record}/edit'),
        ];
    }
}



