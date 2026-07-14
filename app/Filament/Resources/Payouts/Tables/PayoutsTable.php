<?php

namespace App\Filament\Resources\Payouts\Tables;

use App\Filament\Resources\Orders\OrderResource;
use App\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('seller_info')
                    ->label('Verkäufer/in')
                    ->html()
                    ->formatStateUsing(function (Order $record): string {
                        $vendorName = trim((string) ($record->vendor->name ?? '') . ' ' . (string) ($record->vendor->last_name ?? ''));
                        $iban = (string) ($record->vendor->method->iban ?? '');

                        $nameBlock = $vendorName !== ''
                            ? '<div style="font-weight:700;">' . e($vendorName) . '</div>'
                            : '<div style="font-weight:700;color:#b91c1c;">Namen nicht gefunden</div>';

                        $bankBlock = $iban !== ''
                            ? '<div style="margin-top:10px;"><span style="display:block;font-size:8px;text-transform:uppercase;letter-spacing:.03em;">IBAN</span><span style="font-weight:600;word-break:break-all;font-size:12px;">' . e($iban) . '</span></div>'
                            : '<div style="margin-top:10px;padding:8px 10px;border:1px dashed #d1d5db;border-radius:8px;">Keine Bank hinterlegt</div>';

                        return '<div style="display:flex;flex-direction:column;gap:0;line-height:1.35;white-space:normal;">' . $nameBlock . $bankBlock . '</div>';
                    })
                    ->wrap()
                    ->extraAttributes([
                        'style' => 'display:flex;flex-direction:column;align-items:flex-start;white-space:normal;',
                    ]),
                TextColumn::make('buyer_product')
                    ->label('Käufer/in - Produkt')
                    ->html()
                    ->state(function (Order $record): string {
                        $buyer = trim((string) ($record->first_name ?? '') . ' ' . (string) ($record->last_name ?? ''));
                        $addr = trim((string) ($record->street ?? '') . ' ' . (string) ($record->house_no ?? ''));
                        $zipCity = trim((string) ($record->zip ?? '') . ' ' . (string) ($record->federal_state ?? ''));
                        $email = (string) ($record->email ?? '');
                        $poBox = (string) ($record->po_box ?? '');
                        $productName = (string) ($record->product?->name ?: 'Produktname nicht vorhanden');
                        $categoryName = (string) ($record->product?->category?->name ?: 'Kategorie nicht vorhanden');

                        $html = '<div style="font-weight:700;">' . e($buyer !== '' ? $buyer : 'Käufer unbekannt') . '</div>';

                        if (! empty($record->additional)) {
                            $html .= '<div style="margin-top:2px;">' . e((string) $record->additional) . '</div>';
                        }

                        if ($addr !== '') {
                            $html .= '<div style="margin-top:2px;">' . e($addr) . '</div>';
                        }

                        if ($zipCity !== '') {
                            $html .= '<div style="margin-top:2px;">' . e($zipCity) . '</div>';
                        }

                        if ($poBox !== '') {
                            $html .= '<div style="margin-top:2px;">Postfach: ' . e($poBox) . '</div>';
                        }

                        if ($email !== '') {
                            $html .= '<div style="margin-top:6px;"><a href="mailto:' . e($email) . '" target="_blank" style="font-weight:600;text-decoration:none;">' . e($email) . '</a></div>';
                        }

                        $html .= '<div style="margin-top:10px;padding:8px 10px; border-left:3px solid #9ca3af;">'
                            . '<div style="font-size:11px;text-transform:uppercase;letter-spacing:.03em;">Gekauftes Produkt</div>'
                            . '<div style="font-weight:700;">' . e($productName) . '</div>'
                            . '<div style="margin-top:6px;font-size:11px;text-transform:uppercase;letter-spacing:.03em;">Kategorie</div>'
                            . '<div style="font-weight:600;">' . e($categoryName) . '</div>'
                            . '</div>';

                        return '<div style="display:flex;flex-direction:column;gap:0;line-height:1.35;white-space:normal;">' . $html . '</div>';
                    })
                    ->wrap()
                    ->extraAttributes([
                        'style' => 'display:flex;flex-direction:column;align-items:flex-start;white-space:normal;',
                    ])
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search): void {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('id', $search)
                                ->orWhere('parent_id', $search)
                                ->orWhere('tracking_Id', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('id')
                    ->label('Bestell-ID')
                    ->sortable(),
                TextColumn::make('shipping_status')
                    ->label('Versandstatus')
                    ->html()
                    ->state(function (Order $record): string {
                        $shippingMethod = (string) ($record->shipping_method ?? '');
                        $trackingId = (string) ($record->tracking_Id ?? '');

                        $itemStyle = 'display:flex;flex-direction:column;gap:2px;margin-bottom:8px;';
                        $labelStyle = 'display:block;font-size:11px;text-transform:uppercase;letter-spacing:.02em;color:#6b7280;';
                        $valueStyle = 'display:block;font-weight:600;word-break:break-word;';

                        $rows = [];
                        $rows[] = '<div style="' . $itemStyle . '"><span style="' . $labelStyle . '">Versandmethode</span><span style="' . $valueStyle . 'color:' . ($shippingMethod !== '' ? '#15803d' : '#374151') . ';">' . e($shippingMethod !== '' ? $shippingMethod : 'Keine Versandmethode festgelegt') . '</span></div>';
                        $rows[] = '<div style="' . $itemStyle . '"><span style="' . $labelStyle . '">Tracking ID</span><span style="' . $valueStyle . 'color:' . ($trackingId !== '' ? '#15803d' : '#374151') . ';">' . e($trackingId !== '' ? $trackingId : 'Keine Tracking ID vorhanden') . '</span></div>';

                        if (! empty($record->shipping_date)) {
                            $date = $record->shipping_date;

                            if (! $date instanceof \Carbon\CarbonInterface) {
                                $date = \Carbon\Carbon::parse((string) $date);
                            }

                            $rows[] = '<div style="' . $itemStyle . '"><span style="' . $labelStyle . '">Versanddatum</span><span style="' . $valueStyle . 'color:#15803d;">' . e($date->format('d.m.Y')) . '</span></div>';
                        } else {
                            $rows[] = '<div style="' . $itemStyle . '"><span style="' . $labelStyle . '">Versanddatum</span><span style="' . $valueStyle . '">Kein Versanddatum eingegeben</span></div>';
                        }

                        $badges = [];

                        if (($record->video && Storage::exists($record->video)) && (int) ($record->status ?? 0) !== 3) {
                            $badges[] = '<span style="display:inline-block;padding:2px 8px;border-radius:999px;background:#dcfce7;color:#166534;font-size:12px;font-weight:600;">Video versendet</span>';
                        }

                        if ($record->orderimages->isNotEmpty() && (int) ($record->status ?? 0) !== 3) {
                            $badges[] = '<span style="display:inline-block;padding:2px 8px;border-radius:999px;background:#dcfce7;color:#166534;font-size:12px;font-weight:600;">Foto versendet</span>';
                        }

                        if ($badges !== []) {
                            $rows[] = '<div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start;margin-bottom:8px;">' . implode('', $badges) . '</div>';
                        }

                        $trackingUrl = null;
                        if ($shippingMethod === 'DHL') {
                            $trackingUrl = 'https://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=' . urlencode($trackingId);
                        } elseif ($shippingMethod === 'Hermes') {
                            $trackingUrl = 'https://www.myhermes.de/empfangen/sendungsverfolgung/suchen/sendungsinformation/' . urlencode($trackingId);
                        } elseif ($shippingMethod === 'DPD') {
                            $trackingUrl = 'https://tracking.dpd.de/parcelstatus?query=' . urlencode($trackingId) . '&locale=de_DE';
                        } elseif ($shippingMethod === 'UPS') {
                            $trackingUrl = 'http://wwwapps.ups.com/WebTracking/processInputRequest?sort_by=status&tracknums_displayed=1&TypeOfInquiryNumber=T&loc=de_DE&InquiryNumber1=' . urlencode($trackingId) . '&track.x=0&track.y=0';
                        } elseif ($shippingMethod === 'GLS') {
                            $trackingUrl = 'https://www.gls-pakete.de/sendungsverfolgung?match=' . urlencode($trackingId) . '&txtAction=71000';
                        } elseif ($shippingMethod === 'Fedex') {
                            $trackingUrl = 'https://www.fedex.com/fedextrack/?tracknumbers=' . urlencode($trackingId) . '&locale=de_DE&cntry_code=de';
                        }

                        if ($shippingMethod === 'Deutsche Post') {
                            $rows[] = '<a class="fi-btn fi-btn-size-sm fi-color-gray" style="display:flex;flex-direction:column;align-items:flex-start;margin-top:4px;" target="_blank" href="https://www.deutschepost.de/sendung/simpleQuery.html">Zur Seite der DP</a>';
                        } elseif ($shippingMethod === 'Anderes') {
                            $rows[] = '<div style="margin-top:4px;color:#4b5563;">Versendet mit: Anderes, kein Tracking möglich</div>';
                        } elseif ($trackingUrl) {
                            $rows[] = '<a class="fi-btn fi-btn-size-sm fi-color-gray" style="display:flex;flex-direction:column;align-items:flex-start;margin-top:4px;" target="_blank" href="' . e($trackingUrl) . '">Ansehen</a>';
                        }

                        return '<div style="display:flex;flex-direction:column;gap:0;line-height:1.35;white-space:normal;">' . implode('', $rows) . '</div>';
                    })
                    ->wrap()
                    ->grow(false)
                    ->extraAttributes([
                        'style' => 'display:flex;flex-direction:column;align-items:flex-start;white-space:normal;',
                    ]),
                TextColumn::make('payment_info')
                    ->label('Zahlungsinformationen')
                    ->html()
                    ->state(function (Order $record): string {
                        $gateway = (string) ($record->payment_gateway ?? '');
                        $paymentId = (string) ($record->payment_id ?? '');

                        $rows = [];
                        if ($gateway !== '') {
                            $rows[] = '<div style="margin-bottom:10px;"><span style="display:block;font-size:11px;text-transform:uppercase;letter-spacing:.02em;">Zahlungsdienst</span><span style="display:inline-block;padding:2px 8px;border-radius:999px;background:#e0f2fe;color:#075985;font-size:12px;font-weight:700;text-transform:uppercase;">' . e($gateway) . '</span></div>';
                        }

                        if ($paymentId !== '') {
                            $rows[] = '<div><span style="display:block;font-size:11px;text-transform:uppercase;letter-spacing:.02em;">Zahlungs-ID</span><span style="font-weight:600;word-break:break-all;">' . e($paymentId) . '</span></div>';
                        }

                        if ($rows === []) {
                            return '<span style="">-</span>';
                        }

                        return '<div style="display:flex;flex-direction:column;gap:0;line-height:1.35;white-space:normal;">' . implode('', $rows) . '</div>';
                    })
                    ->wrap()
                    ->extraAttributes([
                        'style' => 'display:flex;flex-direction:column;align-items:flex-start;white-space:normal;',
                    ]),
                TextColumn::make('message')
                    ->label('Nachricht')
                    ->html()
                    ->formatStateUsing(fn (Order $record): string => (string) ($record->message ?: '<p>Keine Sonderwünsche</p>'))
                    ->wrap()
                    ->grow(false)
                    ->extraAttributes([
                        'style' => 'width: 320px; min-width: 280px; max-width: 360px; white-space: normal;',
                    ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('payout')
                    ->label('Auszahlen')
                    ->button()
                    ->size('sm')
                    ->color('success')
                    ->icon('heroicon-m-wallet')
                    ->requiresConfirmation()
                    ->modalHeading('Auszahlung bestätigen')
                    ->modalDescription('Bist du sicher, dass du die Bestellung als ausgezahlt markieren möchtest?')
                    ->action(function (Order $record): void {
                        $record->update(['payouts_status' => 1]);

                        Notification::make()
                            ->title('Auszahlung erfolgreich!')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Order $record): bool => (int) ($record->payouts_status ?? 0) === 0),
                Action::make('view')
                    ->label('Ansehen')
                    ->link()
                    ->color('warning')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
                Action::make('cancel')
                    ->label('Stornieren')
                    ->link()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Bestellung stornieren')
                    ->modalDescription('Bist du sicher, dass du die Bestellung stornieren möchtest?')
                    ->url(fn (Order $record): string => route('admin.order.cancel', $record))
                    ->visible(fn (Order $record): bool => (int) ($record->status ?? 0) !== 3),
                Action::make('cancelled')
                    ->label('Storniert')
                    ->link()
                    ->color('gray')
                    ->disabled()
                    ->visible(fn (Order $record): bool => (int) ($record->status ?? 0) === 3),
            ])
            ->toolbarActions([]);
    }
}
