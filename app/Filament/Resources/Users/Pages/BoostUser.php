<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Package;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class BoostUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.pages.boost-user';

    public ?int $packageId = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Zurueck zur Liste')
                ->url(UserResource::getUrl('index')),
        ];
    }

    public function getPackages(): Collection
    {
        return Package::query()
            ->where('type', 'Profile')
            ->orderBy('days')
            ->get();
    }

    public function pushUser(): void
    {
        if (! $this->packageId) {
            Notification::make()
                ->title('Bitte waehle ein Paket aus.')
                ->danger()
                ->send();

            return;
        }

        $package = Package::query()
            ->where('type', 'Profile')
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
                ->title('Profil ist bereits gepusht.')
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
