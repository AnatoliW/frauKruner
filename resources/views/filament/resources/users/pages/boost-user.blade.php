<x-filament-panels::page>
    @php
        $packages = \App\Package::query()
            ->where('type', 'Profile')
            ->orderBy('days')
            ->get();

        $user = $record;

        $recommendedPackageId = $packages->count() >= 3
            ? $packages->sortBy('days')->values()->get(1)?->id
            : $packages->first()?->id;

        $selectedPackage = $packages->first(
            fn ($package) => (int) $package->id === (int) ($packageId ?? 0)
        );

        $profileName = $user->username ?? $user->name ?? 'dieses Profil';

        $priceFormat = static function ($value): string {
            return number_format((float) ($value ?? 0), 2, '.', '') . ' €';
        };
    @endphp

    <style>
        .boost-page {
            max-width: 1050px;
            margin: 0 auto;
        }

        .boost-header {
            margin-bottom: 1.25rem;
        }

        .boost-kicker {
            margin-bottom: 0.45rem;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: rgb(var(--primary-600));
        }

        .dark .boost-kicker {
            color: rgb(var(--primary-400));
        }

        .boost-title {
            margin: 0;
            font-size: clamp(1.45rem, 2.3vw, 2rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.025em;
            color: rgb(var(--gray-950));
        }

        .dark .boost-title {
            color: rgb(var(--gray-50));
        }

        .boost-subtitle {
            margin: 0.65rem 0 0;
            max-width: 700px;
            font-size: 0.9rem;
            line-height: 1.65;
            color: rgb(var(--gray-500));
        }

        .dark .boost-subtitle {
            color: rgb(var(--gray-400));
        }

        .boost-profile-name-inline {
            font-weight: 700;
            color: rgb(var(--gray-800));
        }

        .dark .boost-profile-name-inline {
            color: rgb(var(--gray-200));
        }

        .boost-help {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-bottom: 1.25rem;
        }

        .boost-step {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 999px;
            border: 1px solid rgba(var(--primary-500), 0.22);
            background: rgba(var(--primary-500), 0.08);
            padding: 0.5rem 0.75rem;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.2;
            color: rgb(var(--primary-700));
        }

        .dark .boost-step {
            border-color: rgba(var(--primary-500), 0.28);
            background: rgba(var(--primary-500), 0.14);
            color: rgb(var(--primary-300));
        }

        .boost-step-number {
            display: inline-flex;
            width: 1.35rem;
            height: 1.35rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgb(var(--primary-600));
            color: white;
            font-size: 0.72rem;
            font-weight: 800;
        }

        .boost-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .boost-card {
            position: relative;
            display: flex;
            min-height: 270px;
            cursor: pointer;
            flex-direction: column;
            justify-content: space-between;
            border-radius: 1rem;
            border: 2px solid rgb(var(--gray-200));
            background: rgb(var(--gray-50));
            padding: 1rem;
            transition:
                transform 0.18s ease,
                box-shadow 0.18s ease,
                border-color 0.18s ease,
                background-color 0.18s ease;
        }

        .dark .boost-card {
            border-color: rgba(var(--gray-700), 0.85);
            background: rgba(var(--gray-900), 0.75);
        }

        .boost-card:hover {
            transform: translateY(-2px);
            border-color: rgb(var(--primary-400));
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.12);
        }

        .dark .boost-card:hover {
            border-color: rgb(var(--primary-500));
            box-shadow: 0 18px 38px rgba(0, 0, 0, 0.3);
        }

        .boost-card.active {
            border-color: rgb(var(--primary-600));
            background: rgba(var(--primary-50), 0.9);
            box-shadow:
                0 0 0 4px rgba(var(--primary-500), 0.14),
                0 18px 40px rgba(15, 23, 42, 0.14);
        }

        .dark .boost-card.active {
            border-color: rgb(var(--primary-500));
            background: rgba(var(--primary-950), 0.32);
            box-shadow:
                0 0 0 4px rgba(var(--primary-500), 0.2),
                0 18px 42px rgba(0, 0, 0, 0.38);
        }

        .boost-select-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border-radius: 0.85rem;
            border: 1px solid rgb(var(--gray-200));
            background: rgb(var(--gray-100));
            padding: 0.7rem 0.75rem;
            margin-bottom: 1rem;
        }

        .dark .boost-select-row {
            border-color: rgba(var(--gray-700), 0.85);
            background: rgba(var(--gray-800), 0.75);
        }

        .boost-card.active .boost-select-row {
            border-color: rgb(var(--primary-600));
            background: rgb(var(--primary-600));
            color: white;
        }

        .boost-radio-label {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.86rem;
            font-weight: 800;
            line-height: 1.2;
            color: rgb(var(--gray-800));
        }

        .dark .boost-radio-label {
            color: rgb(var(--gray-100));
        }

        .boost-card.active .boost-radio-label {
            color: white;
        }

        .boost-radio {
            width: 1.1rem;
            height: 1.1rem;
            flex-shrink: 0;
            cursor: pointer;
            accent-color: rgb(var(--primary-600));
        }

        .boost-card.active .boost-radio {
            accent-color: white;
        }

        .boost-selected-mark {
            display: none;
            font-size: 0.8rem;
            font-weight: 900;
            line-height: 1;
            color: white;
        }

        .boost-card.active .boost-selected-mark {
            display: inline-flex;
        }

        .boost-package-name {
            margin: 0;
            font-size: 1.12rem;
            font-weight: 800;
            line-height: 1.3;
            letter-spacing: -0.01em;
            color: rgb(var(--gray-950));
        }

        .dark .boost-package-name {
            color: rgb(var(--gray-50));
        }

        .boost-package-desc {
            margin: 0.5rem 0 0;
            max-width: 92%;
            font-size: 0.86rem;
            line-height: 1.55;
            color: rgb(var(--gray-500));
        }

        .dark .boost-package-desc {
            color: rgb(var(--gray-400));
        }

        .boost-price-area {
            margin-top: 1.35rem;
            text-align: right;
        }

        .boost-price {
            margin: 0;
            font-size: 2.25rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.055em;
            color: rgb(var(--gray-950));
        }

        .dark .boost-price {
            color: rgb(var(--gray-50));
        }

        .boost-days {
            margin-top: 0.35rem;
            font-size: 0.78rem;
            font-weight: 600;
            line-height: 1.3;
            color: rgb(var(--gray-500));
        }

        .dark .boost-days {
            color: rgb(var(--gray-400));
        }

        .boost-card-button {
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            border: 1px solid rgb(var(--primary-600));
            background: transparent;
            color: rgb(var(--primary-700));
            padding: 0.7rem 0.85rem;
            font-size: 0.84rem;
            font-weight: 900;
            line-height: 1.2;
            transition: 0.18s ease;
        }

        .dark .boost-card-button {
            color: rgb(var(--primary-300));
            border-color: rgb(var(--primary-500));
        }

        .boost-card:hover .boost-card-button {
            background: rgba(var(--primary-500), 0.08);
        }

        .boost-card.active .boost-card-button {
            background: rgb(var(--primary-600));
            color: white;
            border-color: rgb(var(--primary-600));
        }

        .boost-footer {
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-radius: 1rem;
            border: 1px solid rgb(var(--gray-200));
            background: rgb(var(--gray-50));
            padding: 1rem;
        }

        .dark .boost-footer {
            border-color: rgba(var(--gray-700), 0.85);
            background: rgba(var(--gray-900), 0.75);
        }

        .boost-summary-title {
            margin: 0;
            font-size: 0.94rem;
            font-weight: 800;
            line-height: 1.3;
            color: rgb(var(--gray-950));
        }

        .dark .boost-summary-title {
            color: rgb(var(--gray-50));
        }

        .boost-summary-text {
            margin: 0.28rem 0 0;
            font-size: 0.83rem;
            line-height: 1.5;
            color: rgb(var(--gray-500));
        }

        .dark .boost-summary-text {
            color: rgb(var(--gray-400));
        }

        .boost-empty {
            border: 1px dashed rgb(var(--gray-300));
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            font-size: 0.9rem;
            color: rgb(var(--gray-500));
        }

        .dark .boost-empty {
            border-color: rgba(var(--gray-700), 0.85);
            color: rgb(var(--gray-400));
        }

        @media (max-width: 1050px) {
            .boost-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .boost-grid {
                grid-template-columns: 1fr;
            }

            .boost-card {
                min-height: 245px;
            }

            .boost-price {
                font-size: 2.05rem;
            }

            .boost-footer {
                align-items: stretch;
                flex-direction: column;
            }

            .boost-footer .fi-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <form wire:submit="pushUser">
        <div class="boost-page">
            <x-filament::section>
                <div class="boost-header">
                    <div class="boost-kicker">
                        Profil Pushen
                    </div>

                    <h2 class="boost-title">
                        Paket auswählen
                    </h2>

                    <p class="boost-subtitle">
                        Wähle ein Push-Paket für
                        <span class="boost-profile-name-inline">{{ $profileName }}</span>.
                        Danach kannst du den Profil-Push starten.
                    </p>
                </div>

                <div class="boost-help">
                    <div class="boost-step">
                        <span class="boost-step-number">1</span>
                        <span>Paket auswählen</span>
                    </div>

                    <div class="boost-step">
                        <span class="boost-step-number">2</span>
                        <span>Push starten</span>
                    </div>
                </div>

                @if ($packages->isEmpty())
                    <div class="boost-empty">
                        Keine Profil-Pakete gefunden.
                    </div>
                @else
                    <div class="boost-grid">
                        @foreach ($packages as $package)
                            @php
                                $active = (int) ($packageId ?? 0) === (int) $package->id;
                                $recommended = (int) $recommendedPackageId === (int) $package->id;
                            @endphp

                            <label class="boost-card {{ $active ? 'active' : '' }}">
                                <div>
                                    <div class="boost-select-row">
                                        <span class="boost-radio-label">
                                            <input
                                                type="radio"
                                                name="package"
                                                class="boost-radio"
                                                wire:model.live="packageId"
                                                value="{{ $package->id }}"
                                            >

                                            @if ($active)
                                                Ausgewählt
                                            @else
                                                Auswählen
                                            @endif
                                        </span>

                                        @if ($active)
                                            <span class="boost-selected-mark">
                                                ✓
                                            </span>
                                        @elseif ($recommended)
                                            <x-filament::badge color="primary">
                                                Empfohlen
                                            </x-filament::badge>
                                        @endif
                                    </div>

                                    <p class="boost-package-name">
                                        {{ $package->name }}
                                    </p>

                                    <p class="boost-package-desc">
                                        Profil für {{ $package->days }} Tage hervorheben.
                                    </p>
                                </div>

                                <div>
                                    <div class="boost-price-area">
                                        <p class="boost-price">
                                            {{ $priceFormat($package->price_with_tax) }}
                                        </p>

                                        <p class="boost-days">
                                            {{ $package->days }} Tage · inkl. MwSt.
                                        </p>
                                    </div>

                                    <div class="boost-card-button">
                                        @if ($active)
                                            ✓ Ausgewählt
                                        @else
                                            Paket auswählen
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif

                <div class="boost-footer">
                    <div>
                        <p class="boost-summary-title">
                            @if ($selectedPackage)
                                {{ $selectedPackage->name }} ausgewählt
                            @else
                                Kein Paket ausgewählt
                            @endif
                        </p>

                        <p class="boost-summary-text">
                            @if ($selectedPackage)
                                {{ $priceFormat($selectedPackage->price_with_tax) }} · {{ $selectedPackage->days }} Tage · inkl. MwSt.
                            @else
                                Bitte wähle zuerst ein Paket aus.
                            @endif
                        </p>
                    </div>

                    <x-filament::button
                        type="submit"
                        color="primary"
                        size="lg"
                        icon="heroicon-m-arrow-up-circle"
                        :disabled="blank($packageId)"
                        wire:loading.attr="disabled"
                        wire:target="pushUser"
                    >
                        <span wire:loading.remove wire:target="pushUser">
                            Push starten
                        </span>

                        <span wire:loading wire:target="pushUser">
                            Wird verarbeitet...
                        </span>
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>
    </form>
</x-filament-panels::page>