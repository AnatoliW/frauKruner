<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetAdminLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\DashboardOverviewWidget;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Table;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->defaultCurrency('eur')
                ->defaultNumberLocale('de_DE')
                ->defaultDateDisplayFormat('d.m.Y')
                ->defaultDateTimeDisplayFormat('d.m.Y');
        });

        DatePicker::configureUsing(function (DatePicker $picker): void {
            $picker->displayFormat('d.m.Y');
        });

        DateTimePicker::configureUsing(function (DateTimePicker $picker): void {
            $picker->displayFormat('d.m.Y');
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Frau Kruner')
            ->brandLogo(asset('assets/img/icons/FxxK-Logo.svg'))
            ->brandLogoHeight('36px')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->maxContentWidth(Width::Full)
            // Globale Suche in der oberen Leiste deaktiviert
            ->globalSearch(false)
            ->renderHook(
                'panels::head.start',
                fn() => <<<'HTML'
        <style>
            /* Hide scrollbar visually but keep scrolling functional */
            .fi-sidebar-nav {
                overflow-y: auto !important;
                scrollbar-width: none !important; /* Firefox */
                -ms-overflow-style: none !important; /* IE/Edge */
            }

            .fi-sidebar-nav::-webkit-scrollbar {
                display: none !important; /* Chrome/Safari */
            }

            /* Collapsed sidebar: icon-only on desktop, expand on hover */
            @media (min-width: 64rem) {
                .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar {
                    transition: width 200ms ease, box-shadow 200ms ease !important;
                }

                .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) {
                    width: var(--collapsed-sidebar-width) !important;
                    z-index: 30;
                }

                .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav {
                    padding-inline: 0.5rem;
                }

                .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar.fi-sidebar-open {
                    z-index: 40;
                    box-shadow: 4px 0 24px -4px rgba(15, 23, 42, 0.15);
                }
            }
            /* Tabellen: Links/Bilder duerfen keinen Browser-Drag starten,
               sonst zeigt Ziehen mit der Maus das Verboten-Symbol statt Text zu markieren. */
            .fi-ta a,
            .fi-ta img {
                -webkit-user-drag: none;
            }

            .fi-ta-cell,
            .fi-ta-text {
                user-select: text;
                -webkit-user-select: text;
            }

            .fi-sidebar-nav-group {
                font-weight: bold;
                color: #374151;
                background: #F3F4F6;
                border-radius: 6px;
                margin-bottom: 8px;
                padding: 4px 8px;
            }
            .fi-sidebar-nav-group-label {
                font-size: 1.1em;
                letter-spacing: 0.5px;
            }

            /* Posts create/edit: centered, large writing area */
            .fi-resource-posts .fi-post-writing-section {
                max-width: 52rem;
                margin-inline: auto;
                width: 100%;
            }

            .fi-resource-posts .fi-fo-rich-editor-main {
                min-height: 60vh;
            }

            .fi-resource-posts .fi-fo-rich-editor-content {
                min-height: 60vh;
                font-size: 1.125rem;
                line-height: 1.75;
            }

            .fi-resource-posts .fi-fo-rich-editor-toolbar .fi-fo-rich-editor-tool[data-tool="attachFiles"] {
                min-width: 2.25rem;
            }

            .fi-page-dashboard [data-filament-page-content] {
                gap: 1rem;
            }

            .fi-page-dashboard .fi-section {
                border-radius: 1rem;
                border: 1px solid rgba(148, 163, 184, 0.25);
                background: linear-gradient(150deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.96));
                box-shadow: 0 14px 30px -24px rgba(15, 23, 42, 0.4);
            }

            .fi-page-dashboard .fi-section .fi-section-header-heading {
                color: #111827;
                font-weight: 700;
            }

            .fi-page-dashboard .fi-input-wrp,
            .fi-page-dashboard .fi-select-input,
            .fi-page-dashboard .fi-select-input-wrp,
            .fi-page-dashboard .fi-input {
                border-radius: 0.85rem;
            }

            .fi-page-dashboard .fi-wi-chart,
            .fi-page-dashboard .fi-ta {
                border-radius: 1rem;
                border: 1px solid rgba(148, 163, 184, 0.2);
                box-shadow: 0 14px 30px -24px rgba(15, 23, 42, 0.35);
            }

            .fi-page-dashboard .fi-ta-header,
            .fi-page-dashboard .fi-ta-header-toolbar {
                background: rgba(249, 250, 251, 0.9);
            }

            /* Orders list: dark segmented tabs similar to requested dashboard style. */
            .fi-resource-orders .fi-tabs {
                border-radius: 14px;
                border: 1px solid rgba(255, 255, 255, 0.12);
                background: linear-gradient(180deg, rgba(30, 32, 38, 0.9), rgba(24, 25, 30, 0.9));
                padding: 0.35rem;
            }

            .fi-resource-orders .fi-tabs .fi-tabs-item-btn {
                border-radius: 10px;
                color: rgba(235, 238, 245, 0.75);
                font-weight: 600;
            }

            .fi-resource-orders .fi-tabs .fi-tabs-item-btn.fi-active {
                background: rgba(59, 130, 246, 0.18);
                color: #dbeafe;
            }

            .fi-resource-orders .fi-ta-actions {
                display: flex !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                justify-content: flex-start !important;
                gap: 0.35rem !important;
                flex-wrap: nowrap !important;
            }

            /* Keep table columns compact on wide layouts (orders/payouts only). */
            .fi-resource-orders .fi-ta-table,
            .fi-resource-payouts .fi-ta-table {
                width: 100%;
                table-layout: auto;
            }

            .fi-resource-orders .fi-ta-table .fi-ta-header-cell,
            .fi-resource-orders .fi-ta-table .fi-ta-cell,
            .fi-resource-payouts .fi-ta-table .fi-ta-header-cell,
            .fi-resource-payouts .fi-ta-table .fi-ta-cell {
                width: auto;
            }

            .fi-resource-orders .fi-ta-table .fi-ta-header-cell.fi-growable,
            .fi-resource-payouts .fi-ta-table .fi-ta-header-cell.fi-growable {
                width: auto;
            }

            .fi-resource-orders .fi-ta-table .fi-ta-col,
            .fi-resource-orders .fi-ta-table .fi-ta-text,
            .fi-resource-payouts .fi-ta-table .fi-ta-col,
            .fi-resource-payouts .fi-ta-table .fi-ta-text {
                width: auto;
            }

            .fi-resource-orders .fi-ta-table th.fi-ta-header-cell,
            .fi-resource-payouts .fi-ta-table th.fi-ta-header-cell {
                width: 1%;
                white-space: nowrap;
            }

            .fi-resource-orders .fi-ta-table td.fi-ta-cell:not(:has(.fi-wrapped)),
            .fi-resource-payouts .fi-ta-table td.fi-ta-cell:not(:has(.fi-wrapped)) {
                width: 1%;
                white-space: nowrap;
            }

            .fi-resource-orders .fi-ta-table .fi-ta-header-cell,
            .fi-resource-payouts .fi-ta-table .fi-ta-header-cell {
                padding: 0.5rem 0.625rem;
            }

            .fi-resource-orders .fi-ta-table .fi-ta-text:not(.fi-inline),
            .fi-resource-payouts .fi-ta-table .fi-ta-text:not(.fi-inline) {
                padding: 0.5rem 0.625rem;
            }

            .fi-resource-orders .fi-ta-table .fi-ta-header-cell:first-of-type,
            .fi-resource-payouts .fi-ta-table .fi-ta-header-cell:first-of-type {
                padding-inline-start: 1rem;
            }

            .fi-resource-orders .fi-ta-table .fi-ta-header-cell:last-of-type,
            .fi-resource-payouts .fi-ta-table .fi-ta-header-cell:last-of-type {
                padding-inline-end: 1rem;
            }

            .fi-resource-orders .fi-ta-table .fi-ta-cell:first-of-type .fi-ta-text:not(.fi-inline),
            .fi-resource-payouts .fi-ta-table .fi-ta-cell:first-of-type .fi-ta-text:not(.fi-inline) {
                padding-inline-start: 1rem;
            }

            .fi-resource-orders .fi-ta-table .fi-ta-cell:last-of-type .fi-ta-text:not(.fi-inline),
            .fi-resource-payouts .fi-ta-table .fi-ta-cell:last-of-type .fi-ta-text:not(.fi-inline) {
                padding-inline-end: 1rem;
            }

            /* Keep bulk-select column aligned between header and body rows. */
            .fi-ta-table .fi-ta-selection-cell,
            .fi-ta-table .fi-ta-actions-header-cell,
            .fi-ta-table .fi-ta-empty-header-cell {
                width: 1%;
                white-space: nowrap;
                text-align: start;
            }

            .fi-ta-record-content .fi-ta-col.fi-growable {
                flex: 0 1 auto;
                width: auto;
            }

            .fi-resource-payouts a.fi-ta-col,
            .fi-resource-payouts .fi-ta-cell-shipping-status,
            .fi-resource-payouts .fi-ta-cell-shipping-status .fi-ta-text,
            .fi-resource-payouts .fi-ta-cell-shipping-status .fi-ta-col,
            .fi-resource-payouts .fi-ta-cell-buyer-product .fi-ta-col,
            .fi-resource-payouts .fi-ta-cell-buyer-product .fi-ta-text,
            .fi-resource-payouts .fi-ta-cell-seller-info .fi-ta-col,
            .fi-resource-payouts .fi-ta-cell-seller-info .fi-ta-text,
            .fi-resource-payouts .fi-ta-cell-payment-info .fi-ta-col,
            .fi-resource-payouts .fi-ta-cell-payment-info .fi-ta-text {
                display: flex !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                white-space: normal !important;
                width: auto !important;
            }

            .fi-resource-payouts a.fi-ta-col {
                text-decoration: none;
                color: inherit;
            }

            .fi-resource-payouts .fi-ta-cell-shipping-status a.fi-btn {
                display: flex !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                width: auto !important;
            }

            .fi-resource-payouts .fi-ta-actions {
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
                justify-content: flex-start !important;
                gap: 0.35rem !important;
                flex-wrap: nowrap !important;
                width: 7.5rem;
                min-width: 7.5rem;
            }

            .fi-resource-payouts .fi-ta-actions .fi-btn {
                width: 100% !important;
                justify-content: center !important;
            }

            /* Keep row actions and collapse button pinned to top for tall records. */
            .fi-ta-content-ctn .fi-ta-content .fi-ta-record.fi-ta-record-actions-top {
                align-items: flex-start !important;
                position: relative;
            }

            .fi-ta-content-ctn .fi-ta-content .fi-ta-record.fi-ta-record-actions-top .fi-ta-actions,
            .fi-ta-content-ctn .fi-ta-content .fi-ta-record.fi-ta-record-actions-top .fi-ta-record-collapse-btn {
                align-self: flex-start !important;
            }

            @media (min-width: 48rem) {
                .fi-ta-content-ctn .fi-ta-content:not(.fi-ta-content-grid) .fi-ta-record.fi-ta-record-actions-top {
                    padding-right: 15rem;
                }

                .fi-ta-content-ctn .fi-ta-content:not(.fi-ta-content-grid) .fi-ta-record.fi-ta-record-actions-top .fi-ta-record-content-ctn {
                    align-items: flex-start !important;
                    width: 100%;
                }

                .fi-ta-content-ctn .fi-ta-content:not(.fi-ta-content-grid) .fi-ta-record.fi-ta-record-actions-top .fi-ta-actions {
                    position: absolute;
                    top: 1rem;
                    right: 3.5rem;
                    /* z-index: 5; */
                }
                .fi-ta-content-ctn .fi-ta-content:not(.fi-ta-content-grid) .fi-ta-record.fi-ta-record-actions-top .fi-ta-record-collapse-btn {
                    position: absolute;
                    top: 7px;
                    right: 1rem;
                    /* z-index: 5; */
                }

                /* .fi-ta-content-ctn .fi-ta-content:not(.fi-ta-content-grid) .fi-ta-record.fi-ta-record-actions-top .fi-ta-record-collapse-btn {
                    position: absolute;
                    top: 1rem;
                    right: 1rem;
                    z-index: 5;
                } */
            }
        </style>
        <script>
            (() => {
                // Tabellen: Browser-Drag von Links/Bildern unterbinden, damit
                // Ziehen mit der Maus Text markiert (Fallback fuer Firefox).
                document.addEventListener('dragstart', (event) => {
                    if (event.target instanceof Element && event.target.closest('.fi-ta')) {
                        event.preventDefault();
                    }
                });

                const cleanViewItemsParams = () => {
                    const url = new URL(window.location.href);

                    if (url.searchParams.get('tableAction') !== 'viewItems') {
                        return;
                    }

                    url.searchParams.delete('tableAction');
                    url.searchParams.delete('tableActionRecord');
                    url.searchParams.delete('tableActionArguments');
                    history.replaceState({}, '', url.toString());
                };

                const tryCleanAfterClose = () => {
                    // Wait for modal close animation to fully complete (Filament uses ~300ms transition)
                    setTimeout(() => {
                        const hasOpenDialog = Array.from(document.querySelectorAll('[role="dialog"]'))
                            .some((el) => el.offsetParent !== null && el.getAttribute('aria-hidden') !== 'true');

                        if (!hasOpenDialog) {
                            cleanViewItemsParams();
                        }
                    }, 350);
                };

                // Close button or backdrop click
                document.addEventListener('click', (event) => {
                    const url = new URL(window.location.href);

                    if (url.searchParams.get('tableAction') !== 'viewItems') {
                        return;
                    }

                    const target = event.target;
                    const isCloseButton = target.closest('[data-dismiss="modal"], button[x-on\\:click*="close"], .fi-modal-close-btn, [x-on\\:click="close"]');
                    const isBackdrop = target.closest('[x-on\\:click="close"]') || target.classList.contains('fi-modal-window') || target === target.closest('[role="dialog"]');

                    if (isCloseButton || isBackdrop) {
                        tryCleanAfterClose();
                    }
                }, true);

                // Escape key
                document.addEventListener('keyup', (event) => {
                    if (event.key === 'Escape') {
                        const url = new URL(window.location.href);

                        if (url.searchParams.get('tableAction') === 'viewItems') {
                            tryCleanAfterClose();
                        }
                    }
                });

                // Livewire fires 'close-modal' dispatch when any modal closes
                document.addEventListener('livewire:initialized', () => {
                    window.addEventListener('close-modal', () => tryCleanAfterClose());
                    Livewire.on('close-modal', () => tryCleanAfterClose());
                });

                const initCollapsedSidebarHover = () => {
                    const DESKTOP_BP = 1024;
                    const PINNED_KEY = 'sidebar-pinned-open';
                    let expandedByHover = false;

                    const getStore = () => window.Alpine?.store('sidebar');
                    const isDesktop = () => window.innerWidth >= DESKTOP_BP;

                    const bindSidebar = () => {
                        const sidebar = document.querySelector('.fi-main-sidebar');
                        if (!sidebar || sidebar.dataset.hoverBound) {
                            return;
                        }

                        sidebar.dataset.hoverBound = '1';

                        sidebar.addEventListener('mouseenter', () => {
                            if (!isDesktop()) {
                                return;
                            }

                            const store = getStore();

                            if (store && !store.isOpen) {
                                expandedByHover = true;
                                store.open();
                            }
                        });

                        sidebar.addEventListener('mouseleave', () => {
                            if (!isDesktop() || !expandedByHover) {
                                return;
                            }

                            getStore()?.close();
                            expandedByHover = false;
                        });
                    };

                    document.addEventListener('click', (event) => {
                        if (event.target.closest('.fi-topbar-open-collapse-sidebar-btn')) {
                            expandedByHover = false;
                            localStorage.setItem(PINNED_KEY, '1');
                        }

                        if (event.target.closest('.fi-topbar-close-collapse-sidebar-btn')) {
                            expandedByHover = false;
                            localStorage.removeItem(PINNED_KEY);
                        }
                    });

                    document.addEventListener('alpine:initialized', () => {
                        bindSidebar();

                        const store = getStore();

                        if (!store || !isDesktop()) {
                            return;
                        }

                        if (!localStorage.getItem(PINNED_KEY)) {
                            store.close();
                        }
                    });

                    document.addEventListener('livewire:navigated', () => {
                        const sidebar = document.querySelector('.fi-main-sidebar');

                        if (sidebar) {
                            delete sidebar.dataset.hoverBound;
                        }

                        bindSidebar();
                    });
                };

                initCollapsedSidebarHover();
            })();
        </script>
    HTML
            )
            ->sidebarCollapsibleOnDesktop(true)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Zahlungen')
                    ->icon(Heroicon::OutlinedCreditCard),

                NavigationGroup::make()
                    ->label('Push')
                    ->icon(Heroicon::OutlinedRocketLaunch),

                NavigationGroup::make()
                    ->label('Produkte')
                    ->icon(Heroicon::OutlinedShoppingBag),

                NavigationGroup::make()
                    ->label('Nutzer')
                    ->icon(Heroicon::OutlinedUsers),

                NavigationGroup::make()
                    ->label('Einstellungen')
                    ->icon(Heroicon::OutlinedCog6Tooth),

                NavigationGroup::make()
                    ->label('Neuigkeiten')
                    ->icon(Heroicon::OutlinedNewspaper),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
                DashboardOverviewWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetAdminLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                RoleMiddleware::class,
            ]);
    }
}
