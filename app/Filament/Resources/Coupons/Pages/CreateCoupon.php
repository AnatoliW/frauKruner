<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    public function getTitle(): string
    {
        return 'Gutschein erstellen';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['used'] = (int) ($data['used'] ?? 0);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return CouponResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
