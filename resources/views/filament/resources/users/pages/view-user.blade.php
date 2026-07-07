<x-filament-panels::page>
    @php
        $user = $this->getRecord()->loadMissing(['profile', 'role', 'verification', 'address']);

        $roleLabel = match ((int) ($user->role_id ?? 0)) {
            3 => 'Verkäufer/in',
            2 => 'Käufer/in',
            1 => 'Administrator',
            default => $user->role?->display_name ?: '-',
        };

        $plainDescription = static function (?string $value): string {
            $plain = html_entity_decode(strip_tags($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return trim(preg_replace('/\s+/u', ' ', $plain) ?? '');
        };

        $yesNo = static fn ($value): string => (int) ($value ?? 0) === 1 ? 'Ja' : 'Nein';
        $activeStatus = static fn ($value): string => (int) ($value ?? 0) === 1 ? 'Aktiv' : 'Pausiert';
        $avatarUrl = filled($user->avatar) && $user->avatar !== 'users/default.png'
            ? media_url($user->avatar)
            : ($user->profile?->profile_img ? media_url($user->profile->profile_img) : asset('images/avatar/04.png'));
    @endphp

    <style>
        .user-detail {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        .user-detail .fi-section {
            border-radius: 0.85rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .user-detail .fi-section-header-heading {
            font-size: 1.125rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.01em;
            color: #182b63 !important;
        }

        .user-detail .fi-section-header {
            padding-bottom: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .user-detail__grid {
            display: grid;
            gap: 1.25rem 1.5rem;
        }

        .user-detail__grid--2 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        .user-detail__grid--3 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        @media (min-width: 640px) {
            .user-detail__grid--2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .user-detail__grid--3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .user-detail__label {
            margin: 0 0 0.35rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #e74a3f;
        }

        .user-detail__value {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 500;
            line-height: 1.5;
            color: #111827;
            word-break: break-word;
        }

        .user-detail__value--multiline {
            white-space: pre-wrap;
        }

        .user-detail__stack {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .user-detail__link {
            display: inline-block;
            margin-top: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }

        .user-detail__link:hover {
            text-decoration: underline;
        }

        .user-detail__avatar {
            height: 8rem;
            width: 8rem;
            border-radius: 9999px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            object-fit: cover;
        }

        .user-detail__verification-image {
            max-height: 12rem;
            border-radius: 0.65rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            object-fit: contain;
        }

        .user-detail__empty {
            margin: 0;
            font-size: 0.9375rem;
            color: #6b7280;
        }
    </style>

    <div class="user-detail mx-auto w-full max-w-5xl">
        <x-filament::section>
            <x-slot name="heading">Allgemein</x-slot>

            <div class="user-detail__grid user-detail__grid--3">
                <div>
                    <p class="user-detail__label">Nutzer-ID</p>
                    <p class="user-detail__value">{{ $user->id }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Nutzername</p>
                    <p class="user-detail__value">{{ $user->username ?: '-' }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Vorname</p>
                    <p class="user-detail__value">{{ $user->name ?: '-' }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Nachname</p>
                    <p class="user-detail__value">{{ $user->last_name ?: '-' }}</p>
                </div>
                <div>
                    <p class="user-detail__label">E-Mail</p>
                    <p class="user-detail__value">{{ $user->email ?: '-' }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Nutzerkategorie</p>
                    <p class="user-detail__value">{{ $roleLabel }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Gewerblich</p>
                    <p class="user-detail__value">{{ $yesNo($user->is_commercial) }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Kommission</p>
                    <p class="user-detail__value">{{ filled($user->commission) ? $user->commission . ' %' : '-' }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Erstellt am</p>
                    <p class="user-detail__value">{{ $user->created_at?->format('d.m.Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Letzter Login</p>
                    <p class="user-detail__value">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d.m.Y H:i') : '-' }}</p>
                </div>
                <div>
                    <p class="user-detail__label">E-Mail verifiziert am</p>
                    <p class="user-detail__value">{{ $user->email_verified_at?->format('d.m.Y H:i') ?? '-' }}</p>
                </div>
            </div>

            @if ((int) $user->role_id === 3)
                <a
                    href="{{ route('user.profile', $user) }}"
                    target="_blank"
                    class="user-detail__link"
                >
                    Profil im Shop ansehen
                </a>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Status & Verifizierung</x-slot>

            <div class="user-detail__grid user-detail__grid--3">
                <div>
                    <p class="user-detail__label">Status</p>
                    <p class="user-detail__value">{{ $activeStatus($user->status) }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Sichtbarkeit</p>
                    <p class="user-detail__value">{{ $yesNo($user->visibiliti_status) }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Verifiziert</p>
                    <p class="user-detail__value">{{ $yesNo($user->verified) }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Verifizierung gelöscht am</p>
                    <p class="user-detail__value">{{ $user->verification_deleted_at ? \Carbon\Carbon::parse($user->verification_deleted_at)->format('d.m.Y H:i') : '-' }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Verifizierung erneut senden</p>
                    <p class="user-detail__value">{{ $user->resend ?? 0 }}×</p>
                </div>
                <div>
                    <p class="user-detail__label">Verifizierungsstatus</p>
                    <p class="user-detail__value">
                        @if ($user->verification)
                            {{ (int) ($user->verification->status ?? 0) === 1 ? 'Abgeschlossen' : 'Ausstehend' }}
                        @else
                            Keine Verifizierung vorhanden
                        @endif
                    </p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Profil</x-slot>

            <div class="user-detail__stack">
                <div>
                    <p class="user-detail__label">Profilbild</p>
                    <img
                        src="{{ $avatarUrl }}"
                        alt="{{ $user->username ?: $user->name }}"
                        class="user-detail__avatar"
                    >
                </div>
                <div>
                    <p class="user-detail__label">Profilbeschreibung</p>
                    <p class="user-detail__value user-detail__value--multiline">{{ $plainDescription($user->profile?->description) ?: '-' }}</p>
                </div>
                @if ((int) $user->role_id === 3)
                    <div>
                        <p class="user-detail__label">Produkte im Katalog</p>
                        <p class="user-detail__value">{{ $user->products()->count() }}</p>
                    </div>
                @endif
            </div>
        </x-filament::section>

        @if ($user->address && collect($user->address->toArray())->filter(fn ($value, $key) => ! in_array($key, ['id', 'user_id', 'created_at', 'updated_at'], true) && filled($value))->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">Adresse & Abrechnung</x-slot>

                <div class="user-detail__grid user-detail__grid--3">
                    <div>
                        <p class="user-detail__label">Vorname</p>
                        <p class="user-detail__value">{{ $user->address->first_name ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="user-detail__label">Nachname</p>
                        <p class="user-detail__value">{{ $user->address->last_name ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="user-detail__label">Straße</p>
                        <p class="user-detail__value">{{ trim(($user->address->street ?? '') . ' ' . ($user->address->house_no ?? '')) ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="user-detail__label">PLZ</p>
                        <p class="user-detail__value">{{ $user->address->zip ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="user-detail__label">Bundesland</p>
                        <p class="user-detail__value">{{ $user->address->federal_state ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="user-detail__label">PayPal E-Mail</p>
                        <p class="user-detail__value">{{ $user->address->paypal_email ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="user-detail__label">USt-IdNr.</p>
                        <p class="user-detail__value">{{ $user->address->vat_id ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="user-detail__label">Postfach</p>
                        <p class="user-detail__value">{{ $user->address->po_box ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="user-detail__label">Zusatz</p>
                        <p class="user-detail__value user-detail__value--multiline">{{ $user->address->additional ?: '-' }}</p>
                    </div>
                </div>
            </x-filament::section>
        @endif

        @if ($user->verification)
            <x-filament::section>
                <x-slot name="heading">Verifizierungsunterlagen</x-slot>

                <div class="user-detail__grid user-detail__grid--3">
                    <div>
                        <p class="user-detail__label">Geburtsdatum</p>
                        <p class="user-detail__value">{{ filled($user->verification->date_of_birth) ? \Carbon\Carbon::parse($user->verification->date_of_birth)->format('d.m.Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="user-detail__label">Ort</p>
                        <p class="user-detail__value">{{ $user->verification->city ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="user-detail__label">PLZ</p>
                        <p class="user-detail__value">{{ $user->verification->zip ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="user-detail__label">Adresse</p>
                        <p class="user-detail__value">{{ trim(($user->verification->street ?? '') . ' ' . ($user->verification->house_no ?? '')) ?: '-' }}</p>
                    </div>
                </div>

                <div class="user-detail__grid user-detail__grid--3 mt-4">
                    @foreach ([
                        'person_id_shot_img' => 'Person mit Ausweis',
                        'id_card_front_img' => 'Ausweis Vorderseite',
                        'id_card_back_img' => 'Ausweis Rückseite',
                    ] as $field => $label)
                        @if (filled($user->verification->{$field}))
                            <div>
                                <p class="user-detail__label">{{ $label }}</p>
                                <img
                                    src="{{ media_url($user->verification->{$field}) }}"
                                    alt="{{ $label }}"
                                    class="user-detail__verification-image"
                                >
                            </div>
                        @endif
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">Push</x-slot>

            <div class="user-detail__grid user-detail__grid--3">
                <div>
                    <p class="user-detail__label">Gepusht</p>
                    <p class="user-detail__value">{{ $yesNo($user->boosted) }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Push-Start</p>
                    <p class="user-detail__value">{{ $user->boost_start_date ? \Carbon\Carbon::parse($user->boost_start_date)->format('d.m.Y H:i') : '-' }}</p>
                </div>
                <div>
                    <p class="user-detail__label">Push-Ende</p>
                    <p class="user-detail__value">{{ $user->boost_end_date ? \Carbon\Carbon::parse($user->boost_end_date)->format('d.m.Y H:i') : '-' }}</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
