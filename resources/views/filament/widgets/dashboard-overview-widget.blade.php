<x-filament-widgets::widget>
    <style>
        .custom-dashboard {
            display: grid;
            gap: 1.5rem;
        }

        .storage-alert {
            border-radius: 0.25rem;
            border: 0;
            background: #f39c12;
            padding: 1.45rem 1.9rem;
            color: #ffffff;
        }

        .storage-alert-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.4;
        }

        .storage-alert-text {
            margin: 0.35rem 0 0;
            font-size: 0.95rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.95);
        }

        .storage-alert-action {
            margin-top: 0.8rem;
        }

        .storage-alert-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.25rem;
            background: #ffffff;
            color: #111827;
            padding: 0.55rem 1.25rem;
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.2;
            transition: 0.18s ease;
        }

        .storage-alert-button:hover {
            background: #f8fafc;
        }

        .dashboard-card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 2.75rem;
        }

        .dashboard-image-card {
            position: relative;
            overflow: hidden;
            min-height: 198px;
            border-radius: 0.2rem;
            border: 1px solid rgba(15, 23, 42, 0.18);
            background: #2d353d;
            color: #ffffff;
            isolation: isolate;
        }

        .dashboard-image-card::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -2;
            background-image: var(--card-image);
            background-size: cover;
            background-position: center;
            transform: scale(1.02);
        }

        /*
        |--------------------------------------------------------------------------
        | Same overlay/background color like screenshot
        |--------------------------------------------------------------------------
        */
        .dashboard-image-card::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                linear-gradient(
                    135deg,
                    rgba(45, 53, 61, 0.82) 0%,
                    rgba(45, 53, 61, 0.70) 42%,
                    rgba(91, 48, 77, 0.62) 100%
                );
        }

        .dashboard-card-content {
            display: flex;
            min-height: 198px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            text-align: center;
        }

        .dashboard-card-icon {
            display: inline-flex;
            width: 5.75rem;
            height: 5.75rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.58);
            color: #ffffff;
            margin-bottom: 1.45rem;
            box-shadow: 0 18px 38px rgba(0, 0, 0, 0.22);
        }

        .dashboard-card-icon svg {
            width: 2.45rem;
            height: 2.45rem;
        }

        .dashboard-card-title {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 400;
            line-height: 1.25;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .dashboard-card-text {
            margin: 1rem 0 0;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .dashboard-card-action {
            margin-top: 1.55rem;
        }

        .dashboard-card-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.25rem;
            border: 1px solid rgba(14, 165, 233, 0.85);
            background: #1ba4db;
            color: #ffffff;
            padding: 0.65rem 1.25rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.2;
            text-decoration: none;
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.12);
            transition: 0.18s ease;
        }

        .dashboard-card-button:hover {
            background: #1593c6;
            color: #ffffff;
        }

        @media (max-width: 900px) {
            .dashboard-card-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .dashboard-image-card,
            .dashboard-card-content {
                min-height: 220px;
            }
        }
    </style>

    <div class="custom-dashboard">
        @if (! $this->hasStorageSymlink())
            <div class="storage-alert">
                <p class="storage-alert-title">
                    Fehlender Speicher-Symlink
                </p>

                <p class="storage-alert-text">
                    Es wurde kein Speicher-Symlink gefunden. Dadurch können Mediendateien im Browser möglicherweise nicht geladen werden.
                </p>

                <div class="storage-alert-action">
                    <button
                        type="button"
                        class="storage-alert-button"
                        wire:click="cleanupStorage"
                    >
                        Bereinigen
                    </button>
                </div>
            </div>
        @endif

        <div class="dashboard-card-grid">
            <div
                class="dashboard-image-card"
                style="--card-image: url('{{ asset('images/dashboard/orders.jpg') }}');"
            >
                <div class="dashboard-card-content">
                    <div class="dashboard-card-icon">
                        <x-filament::icon icon="heroicon-o-ticket" />
                    </div>

                    <h3 class="dashboard-card-title">
                        {{ $this->getOrdersCount() }} Bestellungen
                    </h3>

                    <p class="dashboard-card-text">
                        Sie haben {{ $this->getOrdersCount() }} Bestellungen in Ihrer Datenbank.
                    </p>

                    <div class="dashboard-card-action">
                        <a
                            href="{{ $this->getOrdersUrl() }}"
                            class="dashboard-card-button"
                        >
                            Alle Bestellungen anzeigen
                        </a>
                    </div>
                </div>
            </div>

            <div
                class="dashboard-image-card product-card"
                style="--card-image: url('{{ asset('images/dashboard/products.jpg') }}');"
            >
                <div class="dashboard-card-content">
                    <div class="dashboard-card-icon">
                        <x-filament::icon icon="heroicon-o-archive-box" />
                    </div>

                    <h3 class="dashboard-card-title">
                        {{ $this->getProductsCount() }} Produkte
                    </h3>

                    <p class="dashboard-card-text">
                        Sie haben {{ $this->getProductsCount() }} Produkte in Ihrer Datenbank.
                    </p>

                    <div class="dashboard-card-action">
                        <a
                            href="{{ $this->getProductsUrl() }}"
                            class="dashboard-card-button"
                        >
                            Alle Produkte anzeigen
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>