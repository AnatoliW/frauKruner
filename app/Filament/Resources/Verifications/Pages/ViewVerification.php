<?php

namespace App\Filament\Resources\Verifications\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Verifications\VerificationResource;
use App\Mail\UserNotifyEmail;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ViewVerification extends ViewRecord
{
    protected static string $resource = VerificationResource::class;

    protected string $view = 'filament.resources.verifications.pages.view-verification';

    public function getTitle(): string
    {
        $user = $this->getRecord()->user;
        $label = trim($user?->username ?: trim(($user?->name ?? '') . ' ' . ($user?->last_name ?? '')));

        return $label !== '' ? "Verifizierung: {$label}" : 'Verifizierung';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Zurück zur Übersicht')
                ->url(VerificationResource::getUrl('index')),
            Action::make('approve')
                ->label('Verifizierung bestätigen')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifizierung bestätigen')
                ->modalDescription('Der Nutzer wird freigeschaltet und erhält eine Bestätigungs-E-Mail. Ausweis-Bilder werden danach gelöscht.')
                ->visible(fn (): bool => ! $this->isUserVerified())
                ->action(fn (): mixed => $this->approveVerification()),
            Action::make('reject')
                ->label('Verifizierung ablehnen')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Verifizierung ablehnen')
                ->modalDescription('Der Nutzer wird pausiert und erhält eine Ablehnungs-E-Mail.')
                ->visible(fn (): bool => ! $this->isUserVerified())
                ->action(fn (): mixed => $this->rejectVerification()),
            Action::make('user')
                ->label('Nutzerprofil')
                ->icon('heroicon-m-user')
                ->color('gray')
                ->url(fn (): string => UserResource::getUrl('edit', ['record' => $this->getRecord()->user_id])),
        ];
    }

    public function isUserVerified(): bool
    {
        $user = $this->getRecord()->user;

        return (int) ($user?->verified ?? 0) === 1 && (int) ($user?->status ?? 0) === 1;
    }

    public function approveVerification(): void
    {
        $verification = $this->getRecord()->loadMissing('user');
        $user = $verification->user;

        if (! $user) {
            Notification::make()
                ->title('Nutzer nicht gefunden.')
                ->danger()
                ->send();

            return;
        }

        $user->update([
            'status' => 1,
            'verified' => 1,
        ]);

        $verification->update([
            'status' => 1,
        ]);

        $this->deleteVerificationImages($verification);

        $mailSent = $this->sendVerificationMail($user->email, [
            'subject' => 'Dein Konto wurde erfolgreich verifiziert',
            'title' => 'Dein Konto wurde erfolgreich verifiziert',
            'body' => 'Du kannst nun deine Produkte einstellen oder Käufe tätigen.<br> Ich wünsche dir viel Spaß und tolle Erlebnisse.',
            'button_link' => route('seller.dashboard'),
            'button_text' => 'Login zum Profil',
        ]);

        Notification::make()
            ->title('Verifizierung bestätigt')
            ->body($mailSent
                ? 'Der Nutzer wurde freigeschaltet und per E-Mail informiert.'
                : 'Der Nutzer wurde freigeschaltet. Die Bestätigungs-E-Mail konnte nicht versendet werden.')
            ->color($mailSent ? 'success' : 'warning')
            ->send();

        $this->record->refresh()->loadMissing('user');
    }

    public function rejectVerification(): void
    {
        $verification = $this->getRecord()->loadMissing('user');
        $user = $verification->user;

        if (! $user) {
            Notification::make()
                ->title('Nutzer nicht gefunden.')
                ->danger()
                ->send();

            return;
        }

        $user->update([
            'status' => 0,
            'verified' => 0,
        ]);

        $verification->update([
            'status' => 0,
        ]);

        $mailSent = $this->sendVerificationMail($user->email, [
            'subject' => 'Deine Verifizierung wurde abgelehnt',
            'title' => 'Deine Verifizierung wurde abgelehnt',
            'body' => 'Deine Verifizierung ist fehlgeschlagen.',
            'button_link' => route('home'),
            'button_text' => 'Home',
        ]);

        Notification::make()
            ->title('Verifizierung abgelehnt')
            ->body($mailSent
                ? 'Der Nutzer wurde pausiert und per E-Mail informiert.'
                : 'Der Nutzer wurde pausiert. Die Ablehnungs-E-Mail konnte nicht versendet werden.')
            ->warning()
            ->send();

        $this->record->refresh()->loadMissing('user');
    }

    protected function sendVerificationMail(string $email, array $data): bool
    {
        try {
            Mail::to($email)->send(new UserNotifyEmail($data));

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }

    protected function deleteVerificationImages($verification): void
    {
        $storageDisk = config('voyager.storage.disk', config('filesystems.default'));
        $imagePaths = [
            $verification->person_id_shot_img,
            $verification->id_card_front_img,
            $verification->id_card_back_img,
        ];

        foreach ($imagePaths as $imagePath) {
            if (empty($imagePath)) {
                continue;
            }

            try {
                $disk = Storage::disk($storageDisk);

                if ($disk->exists($imagePath)) {
                    $disk->delete($imagePath);
                }

                if ($storageDisk !== config('filesystems.default')) {
                    $defaultDisk = Storage::disk(config('filesystems.default'));

                    if ($defaultDisk->exists($imagePath)) {
                        $defaultDisk->delete($imagePath);
                    }
                }
            } catch (\Exception $exception) {
                report($exception);
            }
        }

        $verification->update([
            'person_id_shot_img' => null,
            'id_card_front_img' => null,
            'id_card_back_img' => null,
        ]);
    }
}
