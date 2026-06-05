<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RoleMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
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
            .fi-sidebar-nav-group {
                font-weight: bold;
                color: #4F46E5; /* Example: Indigo */
                background: #F3F4F6;
                border-radius: 6px;
                margin-bottom: 8px;
                padding: 4px 8px;
            }
            .fi-sidebar-nav-group-label {
                font-size: 1.1em;
                letter-spacing: 0.5px;
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
                background: linear-gradient(90deg, rgba(252, 245, 241, 0.9), rgba(251, 238, 229, 0.9));
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
                    ->label('Zahlungen'),
                NavigationGroup::make()
                    ->label('Push'),
                NavigationGroup::make()
                    ->label('Produkte'),
                NavigationGroup::make()
                    ->label('Nutzer'),
                NavigationGroup::make()
                    ->label('Gutscheine'),
                NavigationGroup::make()
                    ->label('Einstellungen'),
                NavigationGroup::make()
                    ->label('Neuigkeiten'),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
                RoleMiddleware::class,
            ]);
    }
}
