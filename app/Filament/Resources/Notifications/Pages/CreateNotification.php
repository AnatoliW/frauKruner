<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Filament\Resources\Notifications\NotificationResource;
use App\Support\NotificationEmailSender;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;

    protected int $queuedEmailCount = 0;

    protected bool $emailDispatchFailed = false;

    public function getTitle(): string
    {
        return 'Nachricht erstellen';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['seen'] = 0;
        $data['verified'] = (int) ($data['verified'] ?? 0);
        $data['image'] = filled($data['image'] ?? null) ? $data['image'] : 'notifications/icon.png';

        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            $this->queuedEmailCount = app(NotificationEmailSender::class)->send($this->record);
        } catch (\Throwable $exception) {
            report($exception);
            $this->emailDispatchFailed = true;
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        if ($this->emailDispatchFailed) {
            return Notification::make()
                ->warning()
                ->title('Nachricht gespeichert, E-Mail-Versand fehlgeschlagen')
                ->body('Ein Teil könnte bereits versendet oder eingereiht sein. Bitte den Versand prüfen, bevor du die Nachricht erneut erstellst.')
                ->persistent();
        }

        if ((int) $this->record->is_email === 1) {
            if ($this->queuedEmailCount === 0) {
                return Notification::make()
                    ->warning()
                    ->title('Nachricht gespeichert, keine E-Mail-Empfänger gefunden')
                    ->body('Die Auswahl enthält keine Nutzer mit gültiger E-Mail-Adresse.');
            }

            return Notification::make()
                ->success()
                ->title('Nachricht gespeichert')
                ->body("E-Mail-Versand für {$this->queuedEmailCount} Empfänger angestoßen.");
        }

        return parent::getCreatedNotification();
    }

    protected function getRedirectUrl(): string
    {
        return NotificationResource::getUrl('index');
    }
}
