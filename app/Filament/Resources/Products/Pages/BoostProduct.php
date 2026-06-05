<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Package;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class BoostProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.resources.products.pages.boost-product';

    public ?int $packageId = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Zurueck zur Liste')
                ->url(ProductResource::getUrl('index')),
        ];
    }

    public function getPackages(): Collection
    {
        return Package::query()
            ->where('type', 'Product')
            ->orderBy('days')
            ->get();
    }

    public function pushProduct(): void
    {
        if (! $this->packageId) {
            Notification::make()
                ->title('Bitte waehle ein Paket aus.')
                ->danger()
                ->send();

            return;
        }

        $package = Package::query()
            ->where('type', 'Product')
            ->find($this->packageId);

        if (! $package) {
            Notification::make()
                ->title('Paket wurde nicht gefunden.')
                ->danger()
                ->send();

            return;
        }

        if ((int) ($this->record->boosted ?? 0) === 1) {
            Notification::make()
                ->title('Produkt ist bereits gepusht.')
                ->warning()
                ->send();

            return;
        }

        $boost = $this->record->boosts()->create([
            'package_id' => $package->id,
            'user_id' => Auth::id(),
            'price' => $package->price,
            'base_price' => $package->price,
            'start_day' => Carbon::now(),
            'end_day' => Carbon::now()->addDays($package->days),
        ]);

        $payment = $boost->charge($package->price);

        $this->redirect(route('admin.payment', $payment), navigate: true);
    }
}
