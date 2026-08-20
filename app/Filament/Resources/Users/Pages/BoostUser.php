<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Boosts\BoostResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Boost;
use App\Package;
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

        // Ein Push aus dem Adminbereich ist immer kostenlos: Es entsteht keine
        // Zahlung und keine Rechnung, der Push wird sofort aktiviert.
        $boost = Boost::freeAdminPush($this->record, $package, Auth::id());

        Notification::make()
            ->title('Profil wurde kostenlos gepusht.')
            ->body('Der Push laeuft bis zum ' . $boost->end_day->format('d.m.Y') . '.')
            ->success()
            ->send();

        $this->redirect(BoostResource::getUrl('index'), navigate: true);
    }
}
