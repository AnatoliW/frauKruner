<x-filament-panels::page>
    @php
        $product = $this->getRecord()->loadMissing(['user', 'category', 'images']);

        $fmt = static fn ($value): string => sprintf('%.2f €', (float) ($value ?? 0));

        $finishingNames = \App\Finishing::query()->pluck('name', 'id');
        $additionNames = \App\Addition::query()->pluck('name', 'id');
        $wearingTimeNames = \App\WearingTime::query()->pluck('name', 'id');

        $formatProductOptions = static function ($items, $nameLookup) use ($fmt): string {
            if (! is_array($items) || $items === []) {
                return '-';
            }

            $resolveLabel = static function ($key) use ($nameLookup): string {
                $keyString = (string) $key;

                if (! ctype_digit($keyString)) {
                    return $keyString;
                }

                return $nameLookup[(int) $keyString] ?? $keyString;
            };

            $parts = [];

            if (array_is_list($items)) {
                foreach ($items as $value) {
                    $valueString = (string) $value;

                    $parts[] = ctype_digit($valueString)
                        ? ($nameLookup[(int) $valueString] ?? $valueString)
                        : $valueString;
                }
            } else {
                foreach ($items as $key => $value) {
                    $label = $resolveLabel($key);

                    if (filled($value) && is_numeric($value)) {
                        $parts[] = sprintf('%s (%s)', $label, $fmt($value));
                    } else {
                        $parts[] = $label;
                    }
                }
            }

            $text = implode(', ', array_filter($parts, static fn ($part) => $part !== ''));

            return $text !== '' ? $text : '-';
        };

        $productStatus = (int) $product->getRawOriginal('status') === 1 ? 'Aktiv' : 'Inaktiv';
        $sellerName = trim(($product->user?->name ?? '') . ' ' . ($product->user?->last_name ?? ''));
    @endphp

    <style>
        .product-detail {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        .product-detail .fi-section {
            border-radius: 0.85rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .product-detail .fi-section-header-heading {
            font-size: 1.125rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.01em;
            color: #182b63 !important;
        }

        .product-detail .fi-section-header {
            padding-bottom: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .product-detail__grid {
            display: grid;
            gap: 1.25rem 1.5rem;
        }

        .product-detail__grid--3 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        @media (min-width: 640px) {
            .product-detail__grid--2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .product-detail__grid--3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .product-detail__field + .product-detail__field {
            margin-top: 0;
        }

        .product-detail__label {
            margin: 0 0 0.35rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #e74a3f;
        }

        .product-detail__value {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 500;
            line-height: 1.5;
            color: #111827;
            word-break: break-word;
        }

        .product-detail__value--multiline {
            white-space: pre-wrap;
        }

        .product-detail__stack {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .product-detail__link {
            display: inline-block;
            margin-top: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }

        .product-detail__link:hover {
            text-decoration: underline;
        }

        .product-detail__images {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .product-detail__image-main {
            max-height: 20rem;
            border-radius: 0.65rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            object-fit: contain;
        }

        .product-detail__image-thumb {
            height: 8rem;
            width: 8rem;
            border-radius: 0.65rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            object-fit: cover;
        }

        .product-detail__empty {
            margin: 0;
            font-size: 0.9375rem;
            color: #6b7280;
        }
    </style>

    <div class="product-detail mx-auto w-full max-w-5xl">
        <x-filament::section>
            <x-slot name="heading">Allgemein</x-slot>

            <div class="product-detail__grid product-detail__grid--3">
                <div class="product-detail__field">
                    <p class="product-detail__label">Produkt-ID</p>
                    <p class="product-detail__value">{{ $product->id }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Produktname</p>
                    <p class="product-detail__value">{{ $product->name ?: '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Slug</p>
                    <p class="product-detail__value">{{ $product->slug ?: '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Kategorie</p>
                    <p class="product-detail__value">{{ $product->category?->name ?: '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Verkäuferin</p>
                    <p class="product-detail__value">{{ $sellerName !== '' ? $sellerName : '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Nutzer-ID</p>
                    <p class="product-detail__value">{{ $product->user_id ?: '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Produktstatus</p>
                    <p class="product-detail__value">{{ $productStatus }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Verfügbare Menge</p>
                    <p class="product-detail__value">{{ $product->quantity ?? '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Aufrufe</p>
                    <p class="product-detail__value">{{ $product->view ?? 0 }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Verkaufszähler</p>
                    <p class="product-detail__value">{{ $product->sale_count ?? '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Erstellt am</p>
                    <p class="product-detail__value">{{ $product->created_at?->format('d.m.Y H:i') ?? '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Gelöscht am</p>
                    <p class="product-detail__value">{{ $product->deleted_at?->format('d.m.Y H:i') ?? '-' }}</p>
                </div>
            </div>

            @if ($product->slug)
                <a
                    href="{{ route('product', $product->slug) }}"
                    target="_blank"
                    class="product-detail__link"
                >
                    Produkt im Shop ansehen
                </a>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Preise & Versand</x-slot>

            <div class="product-detail__grid product-detail__grid--3">
                <div class="product-detail__field">
                    <p class="product-detail__label">Basispreis</p>
                    <p class="product-detail__value">{{ $fmt($product->price) }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Angebotspreis</p>
                    <p class="product-detail__value">
                        @if ($product->saleprice)
                            {{ is_numeric($product->saleprice) ? $fmt($product->saleprice) : $product->saleprice . ' €' }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Versandkosten</p>
                    <p class="product-detail__value">{{ $fmt($product->shipping_cost) }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Rabatt</p>
                    <p class="product-detail__value">{{ filled($product->discount) ? $product->discount : '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Verkaufsoption</p>
                    <p class="product-detail__value">{{ $product->selloption ?? '-' }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Beschreibung</x-slot>

            <div class="product-detail__stack">
                <div class="product-detail__field">
                    <p class="product-detail__label">Kurzinfo</p>
                    <p class="product-detail__value product-detail__value--multiline">{{ $product->details ?: '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Beschreibung</p>
                    <p class="product-detail__value product-detail__value--multiline">{{ $product->description ?: '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Tags</p>
                    <p class="product-detail__value">{{ $product->tags ?: '-' }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Optionen</x-slot>

            <div class="product-detail__grid product-detail__grid--3">
                <div class="product-detail__field">
                    <p class="product-detail__label">Veredelungen</p>
                    <p class="product-detail__value">{{ $formatProductOptions($product->finishings, $finishingNames) }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Zusatzoptionen</p>
                    <p class="product-detail__value">{{ $formatProductOptions($product->addition, $additionNames) }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Tragedauer</p>
                    <p class="product-detail__value">{{ $formatProductOptions($product->wearing_time, $wearingTimeNames) }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Push</x-slot>

            <div class="product-detail__grid product-detail__grid--3">
                <div class="product-detail__field">
                    <p class="product-detail__label">Gepusht</p>
                    <p class="product-detail__value">{{ (int) ($product->boosted ?? 0) === 1 ? 'Ja' : 'Nein' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Push-Start</p>
                    <p class="product-detail__value">{{ $product->boost_start_date ? \Carbon\Carbon::parse($product->boost_start_date)->format('d.m.Y H:i') : '-' }}</p>
                </div>
                <div class="product-detail__field">
                    <p class="product-detail__label">Push-Ende</p>
                    <p class="product-detail__value">{{ $product->boost_end_date ? \Carbon\Carbon::parse($product->boost_end_date)->format('d.m.Y H:i') : '-' }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Bilder</x-slot>

            @if (! empty($product->image))
                <div class="product-detail__field">
                    <p class="product-detail__label">Hauptbild</p>
                    <img
                        src="{{ media_url($product->image) }}"
                        alt="{{ $product->name }}"
                        class="product-detail__image-main"
                    >
                </div>
            @endif

            @if ($product->images->isNotEmpty())
                <div class="product-detail__field {{ ! empty($product->image) ? 'mt-4' : '' }}">
                    <p class="product-detail__label">Weitere Bilder</p>
                    <div class="product-detail__images">
                        @foreach ($product->images as $image)
                            @if (! empty($image->image))
                                <img
                                    src="{{ media_url($image->image) }}"
                                    alt="{{ $product->name }}"
                                    class="product-detail__image-thumb"
                                >
                            @endif
                        @endforeach
                    </div>
                </div>
            @elseif (empty($product->image))
                <p class="product-detail__empty">Keine Bilder vorhanden.</p>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
