<x-filament-panels::page>
    @php
        $packages = $this->getPackages();
        $product = $this->getRecord();
        $recommendedPackageId = $packages->count() >= 3
            ? $packages->sortBy('days')->values()->get(1)?->id
            : $packages->first()?->id;

        $priceFormat = static function ($value): string {
            return number_format((float) ($value ?? 0), 2, '.', '') . ' €';
        };
    @endphp

    <style>
        .boost-wrap {
            max-width: 1080px;
            margin: 0 auto;
            padding: 8px 4px;
        }

        .boost-header {
            margin: 0 0 18px;
        }

        .boost-eyebrow {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            font-weight: 700;
            margin-bottom: 10px;
            opacity: .72;
        }

        .boost-heading {
            font-size: clamp(24px, 2.4vw, 34px);
            line-height: 1.08;
            margin: 0;
            font-weight: 700;
            max-width: 760px;
        }

        .boost-subtitle {
            margin: 12px 0 0;
            font-size: 14px;
            opacity: .72;
            max-width: 760px;
        }

        .boost-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin: 20px 0 26px;
        }

        .boost-card {
            position: relative;
            border: 1px solid;
            border-radius: 16px;
            padding: 16px 16px 14px;
            min-height: 186px;
            cursor: pointer;
            transition: box-shadow .22s ease, transform .22s ease, opacity .22s ease;
            opacity: .96;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            backdrop-filter: blur(2px);
        }

        .boost-card:hover {
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.14);
            transform: translateY(-3px);
            opacity: 1;
        }

        .boost-card.active {
            box-shadow: 0 0 0 2px currentColor, 0 12px 26px rgba(0, 0, 0, 0.14);
            opacity: 1;
        }

        .boost-card:focus-within {
            box-shadow: 0 0 0 3px currentColor;
        }

        .boost-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border: 1px solid;
            border-radius: 999px;
            padding: 3px 8px;
            opacity: .78;
        }

        .boost-card-top {
            display: block;
        }

        .boost-card input[type="radio"] {
            width: 16px;
            height: 16px;
            margin-bottom: 10px;
        }

        .boost-title {
            font-size: clamp(34px, 3.1vw, 46px);
            line-height: 1.02;
            margin: 10px 0 0;
            text-align: right;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .boost-name {
            font-size: 22px;
            margin: 0 0 8px;
            font-weight: 700;
            line-height: 1.2;
        }

        .boost-desc {
            font-size: 14px;
            margin: 0;
            opacity: .75;
            line-height: 1.45;
        }

        .boost-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .boost-submit {
            border: 1px solid;
            border-radius: 12px;
            font-size: 22px;
            font-weight: 700;
            line-height: 1;
            padding: 12px 20px;
            cursor: pointer;
            display: inline-flex;
            gap: 8px;
            align-items: center;
            background: transparent;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.16);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .boost-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.2);
        }

        .boost-submit:focus-visible {
            outline: 2px solid currentColor;
            outline-offset: 2px;
        }

        .boost-note {
            font-size: 13px;
            opacity: .8;
        }

        @media (max-width: 1100px) {
            .boost-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .boost-wrap {
                padding: 2px 0;
            }

            .boost-heading {
                font-size: 22px;
            }

            .boost-grid {
                grid-template-columns: 1fr;
            }

            .boost-submit {
                font-size: 20px;
            }

            .boost-title {
                font-size: 34px;
            }

            .boost-name {
                font-size: 20px;
            }
        }
    </style>

    <form wire:submit="pushProduct">
        <div class="boost-wrap">
            <div class="boost-header">
                <div class="boost-eyebrow">Produkt Pushen</div>
                <h2 class="boost-heading">Waehle ein Paket fuer {{ $product->name ?? 'dieses Produkt' }}</h2>
                <p class="boost-subtitle">Design und Ablauf sind gleich wie im bisherigen Push-Screen, jetzt direkt in Filament.</p>
            </div>

            <div class="boost-grid">
                @foreach ($packages as $package)
                    @php
                        $active = (int) $this->packageId === (int) $package->id;
                        $recommended = (int) $recommendedPackageId === (int) $package->id;
                    @endphp
                    <label class="boost-card {{ $active ? 'active' : '' }}">
                        @if ($recommended)
                            <span class="boost-badge">Empfohlen</span>
                        @endif
                        <div class="boost-card-top">
                        <input
                            type="radio"
                            name="package"
                            wire:model.live="packageId"
                            value="{{ $package->id }}"
                        >
                        <p class="boost-name">{{ $package->name }}</p>
                        <p class="boost-desc">das Profil fuer {{ $package->days }} Tage pushen</p>
                        </div>
                        <p class="boost-title">{{ $priceFormat($package->price) }}</p>
                    </label>
                @endforeach
            </div>

            <div class="boost-actions">
                <button type="submit" class="boost-submit">
                    <span>↟</span>
                    <span>Push</span>
                </button>
                <span class="boost-note">Nach dem Push wirst du direkt zur Zahlungsseite weitergeleitet.</span>
            </div>
        </div>
    </form>
</x-filament-panels::page>
