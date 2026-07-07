<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Resources\Coupons\CouponResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    public function getTitle(): string
    {
        return 'Gutschein bearbeiten';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Löschen'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return CouponResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
