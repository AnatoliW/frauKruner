<x-filament-panels::page>
    @php
        $verification = $this->getRecord()->loadMissing(['user.role']);
        $user = $verification->user;

        $roleLabel = match ((int) ($user?->role_id ?? 0)) {
            3 => 'Verkäufer/in',
            2 => 'Käufer/in',
            1 => 'Administrator',
            default => $user?->role?->display_name ?: '-',
        };

        $isVerified = $this->isUserVerified();
        $displayName = trim($user?->username ?: trim(($user?->name ?? '') . ' ' . ($user?->last_name ?? '')));
    @endphp

    <style>
        .verification-detail {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        .verification-detail .fi-section {
            border-radius: 0.85rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .verification-detail .fi-section-header-heading {
            font-size: 1.125rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.01em;
            color: #182b63 !important;
        }

        .verification-detail .fi-section-header {
            padding-bottom: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .verification-detail__status {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-radius: 0.85rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
        }

        .verification-detail__status--pending {
            background: linear-gradient(135deg, rgba(254, 243, 199, 0.85), rgba(255, 251, 235, 0.95));
            border-color: rgba(245, 158, 11, 0.35);
        }

        .verification-detail__status--approved {
            background: linear-gradient(135deg, rgba(209, 250, 229, 0.85), rgba(236, 253, 245, 0.95));
            border-color: rgba(16, 185, 129, 0.35);
        }

        .verification-detail__status--rejected {
            background: linear-gradient(135deg, rgba(254, 226, 226, 0.85), rgba(255, 241, 242, 0.95));
            border-color: rgba(239, 68, 68, 0.35);
        }

        .verification-detail__status-text h2 {
            margin: 0 0 0.35rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
        }

        .verification-detail__status-text p {
            margin: 0;
            font-size: 0.9375rem;
            color: #4b5563;
        }

        .verification-detail__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .verification-detail__grid {
            display: grid;
            gap: 1.25rem 1.5rem;
        }

        .verification-detail__grid--3 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        @media (min-width: 1024px) {
            .verification-detail__grid--3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .verification-detail__label {
            margin: 0 0 0.35rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #e74a3f;
        }

        .verification-detail__value {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 500;
            line-height: 1.5;
            color: #111827;
            word-break: break-word;
        }

        .verification-detail__images {
            display: grid;
            gap: 1.25rem;
        }

        @media (min-width: 768px) {
            .verification-detail__images {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .verification-detail__image-card {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .verification-detail__image {
            width: 100%;
            max-height: 22rem;
            border-radius: 0.65rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            object-fit: contain;
            background: #f8fafc;
        }

        .verification-detail__link {
            display: inline-block;
            margin-top: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }

        .verification-detail__link:hover {
            text-decoration: underline;
        }

        .verification-detail__empty {
            margin: 0;
            font-size: 0.9375rem;
            color: #6b7280;
        }
    </style>

    <div class="verification-detail mx-auto w-full max-w-5xl">
        <div @class([
            'verification-detail__status',
            'verification-detail__status--approved' => $isVerified,
            'verification-detail__status--pending' => ! $isVerified,
        ])>
            <div class="verification-detail__status-text">
                @if ($isVerified)
                    <h2>Verifizierung bestätigt</h2>
                    <p>{{ $displayName !== '' ? $displayName : 'Dieser Nutzer' }} ist freigeschaltet und kann verkaufen.</p>
                @else
                    <h2>Prüfung ausstehend</h2>
                    <p>Bitte Ausweis und Angaben prüfen und anschließend bestätigen oder ablehnen.</p>
                @endif
            </div>

            @if (! $isVerified)
                <div class="verification-detail__actions">
                    <x-filament::button
                        color="success"
                        icon="heroicon-m-check-circle"
                        wire:click="mountAction('approve')"
                    >
                        Bestätigen
                    </x-filament::button>

                    <x-filament::button
                        color="danger"
                        icon="heroicon-m-x-circle"
                        outlined
                        wire:click="mountAction('reject')"
                    >
                        Ablehnen
                    </x-filament::button>
                </div>
            @endif
        </div>

        <x-filament::section>
            <x-slot name="heading">Nutzer</x-slot>

            <div class="verification-detail__grid verification-detail__grid--3">
                <div>
                    <p class="verification-detail__label">Nutzer-ID</p>
                    <p class="verification-detail__value">{{ $user?->id ?: '-' }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">Name</p>
                    <p class="verification-detail__value">{{ $displayName !== '' ? $displayName : '-' }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">E-Mail</p>
                    <p class="verification-detail__value">{{ $user?->email ?: '-' }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">Rolle</p>
                    <p class="verification-detail__value">{{ $roleLabel }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">Nutzerstatus</p>
                    <p class="verification-detail__value">{{ (int) ($user?->status ?? 0) === 1 ? 'Aktiv' : 'Pausiert' }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">Verifiziert</p>
                    <p class="verification-detail__value">{{ (int) ($user?->verified ?? 0) === 1 ? 'Ja' : 'Nein' }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">Verifizierung eingereicht am</p>
                    <p class="verification-detail__value">{{ $verification->created_at?->format('d.m.Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">Verifizierungs-ID</p>
                    <p class="verification-detail__value">{{ $verification->id }}</p>
                </div>
            </div>

            @if ($user)
                <a
                    href="{{ \App\Filament\Resources\Users\UserResource::getUrl('edit', ['record' => $user]) }}"
                    class="verification-detail__link"
                >
                    Nutzerprofil im Admin öffnen
                </a>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Angaben zur Verifizierung</x-slot>

            <div class="verification-detail__grid verification-detail__grid--3">
                <div>
                    <p class="verification-detail__label">Straße</p>
                    <p class="verification-detail__value">{{ $verification->street ?: '-' }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">Hausnummer</p>
                    <p class="verification-detail__value">{{ $verification->house_no ?: '-' }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">PLZ</p>
                    <p class="verification-detail__value">{{ $verification->zip ?: '-' }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">Stadt</p>
                    <p class="verification-detail__value">{{ $verification->city ?: '-' }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">Geburtsdatum</p>
                    <p class="verification-detail__value">{{ filled($verification->date_of_birth) ? \Carbon\Carbon::parse($verification->date_of_birth)->format('d.m.Y') : '-' }}</p>
                </div>
                <div>
                    <p class="verification-detail__label">Verifizierungsstatus</p>
                    <p class="verification-detail__value">{{ (int) ($verification->status ?? 0) === 1 ? 'Verifiziert' : 'Ausstehend' }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Ausweis & Verifizierungsfotos</x-slot>

            @php
                $images = [
                    'person_id_shot_img' => 'Person mit Ausweis',
                    'id_card_front_img' => 'Ausweis Vorderseite',
                    'id_card_back_img' => 'Ausweis Rückseite',
                ];
                $hasImages = collect($images)->keys()->contains(fn (string $field): bool => filled($verification->{$field}));
            @endphp

            @if ($hasImages)
                <div class="verification-detail__images">
                    @foreach ($images as $field => $label)
                        @if (filled($verification->{$field}))
                            <div class="verification-detail__image-card">
                                <p class="verification-detail__label">{{ $label }}</p>
                                <a href="{{ media_url($verification->{$field}) }}" target="_blank">
                                    <img
                                        src="{{ media_url($verification->{$field}) }}"
                                        alt="{{ $label }}"
                                        class="verification-detail__image"
                                    >
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="verification-detail__empty">
                    @if ($isVerified)
                        Die Verifizierungsfotos wurden nach Bestätigung gelöscht.
                    @else
                        Keine Verifizierungsfotos vorhanden.
                    @endif
                </p>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
