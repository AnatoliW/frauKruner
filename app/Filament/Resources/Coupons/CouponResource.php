<?php

namespace App\Filament\Resources\Coupons;

use App\Coupon;
use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Resources\Coupons\Tables\CouponsTable;
use App\Filament\Resources\BaseAdminResource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CouponResource extends BaseAdminResource
{
    protected static ?string $model = Coupon::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Gutscheine';

    protected static ?string $modelLabel = 'Gutschein';

    protected static ?string $pluralModelLabel = 'Gutscheine';

    protected static string|\UnitEnum|null $navigationGroup = 'Gutscheine';

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return CouponForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
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
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}



