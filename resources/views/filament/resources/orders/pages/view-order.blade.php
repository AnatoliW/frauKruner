<x-filament-panels::page>
    @php
        $order = $this->getRecord();

        $fmt = static function ($value): string {
            return sprintf('%.2f €', (float) ($value ?? 0));
        };

        $listText = static function ($items): string {
            if (is_array($items)) {
                return implode(', ', array_map(static fn ($item) => (string) $item, $items));
            }

            if (is_object($items)) {
                return implode(', ', array_map(static fn ($item) => (string) $item, (array) $items));
            }

            if (is_string($items) && trim($items) !== '') {
                return $items;
            }

            return '-';
        };

        $buyerTotal = $order->parent ? ($order->parent->total - ($order->discount > 0 ? $order->discount : 0)) : $order->total;
    @endphp

    <style>
        .order-page {
            /* color: #1f2937; */
            font-size: 13px;
            line-height: 1.45;
        }

        .order-section {
            border: 1px solid #81838B;
            /* border-radius: 8px; */
            margin-bottom: 18px;
            padding: 14px 16px;
        }

        .order-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 500;
            margin: 0 0 14px;
            /* color: #111827; */
        }

        .print-btn {
            border: 1px solid #1f2937;
            border-radius: 4px;
            /* background: #1f2937; */
            /* color: #fff; */
            padding: 2px 10px;
            font-size: 12px;
            line-height: 1.4;
            cursor: pointer;
        }

        .order-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-bottom: 12px;
        }

        .label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            /* color: #111827; */
        }

        .order-table-wrap {
            overflow-x: auto;
            margin-top: 8px;
            scrollbar-width: none;
        }

        .order-table-wrap::-webkit-scrollbar {
            display: none;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .order-table thead th {
            /* background: #f3f4f6; */
            /* color: #374151; */
            text-align: left;
            font-weight: 500;
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
        }

        .order-table tbody td {
            border: 1px solid #e5e7eb;
            padding: 7px 8px;
            vertical-align: top;
        }

        .muted-note {
            margin-top: 8px;
            /* color: #4b5563; */
            font-size: 12px;
        }

        @media (max-width: 900px) {
            .order-grid {
                grid-template-columns: 1fr;
            }

            .order-section-title {
                font-size: 20px;
            }
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .order-page,
            .order-page * {
                visibility: visible;
            }

            .order-page {
                position: absolute;
                inset: 0;
                background: #fff;
                font-size: 12px;
                line-height: 1.4;
                padding: 0;
                margin: 0;
            }

            .order-section {
                border: 1px solid #81838B;
                margin: 0 0 12px;
                padding: 10px 12px;
                break-inside: avoid;
            }

            .order-section-title {
                color: #182b63;
                font-size: 32px;
                font-weight: 500;
                margin-bottom: 10px;
            }

            .label {
                color: #182b63;
                font-size: 20px;
                margin-bottom: 6px;
            }

            .order-table {
                font-size: 16px;
                width: 100% !important;
                table-layout: fixed;
            }

            .order-table-wrap {
                overflow: visible !important;
            }

            .order-table thead th {
                background: #eef1f6;
                color: #182b63;
                white-space: normal;
                word-break: break-word;
            }

            .order-table tbody td {
                white-space: normal;
                word-break: break-word;
            }

            .print-btn {
                display: none !important;
            }
        }
    </style>

    <div class="order-page">
        <section class="order-section">
            <h2 class="order-section-title">Admin Infos
                <button type="button" class="print-btn" onclick="window.print()">Drucken</button>
            </h2>

            <div class="order-grid">
                <div>
                    <p class="label">Kaeufer</p>
                    <p>
                        {{ $order->first_name }} {{ $order->last_name }}<br>
                        {{ $order->street }} {{ $order->house_no }}<br>
                        {{ $order->zip }} {{ $order->federal_state }}<br>
                        @if ($order->po_box)
                            Postfach: {{ $order->po_box }}<br>
                        @endif
                        {{ $order->user?->email ?? $order->email }}
                    </p>
                </div>
            </div>

            <div class="order-table-wrap">
                <p class="label">Details</p>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Produktname</th>
                            <th>Veredelungen</th>
                            <th>Zusatzoptionen</th>
                            <th>Tragedauer</th>
                            <th>Versandpauschale</th>
                            <th>Basispreis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $order->product_name ?? $order->product?->name ?? '-' }}</td>
                            <td>{{ $listText($order->finishings) }}</td>
                            <td>{{ $listText($order->addition) }}</td>
                            <td>{{ $listText($order->wearing_time) }}</td>
                            <td>{{ $fmt($order->shipping_cost) }}</td>
                            <td>{{ $fmt($order->subtotal) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="order-table-wrap">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Plattformgebuehr</th>
                            <th>Produktpreis</th>
                            <th>Preis inkl. Plattformgebuehr</th>
                            <th>Gesamtbetrag (vom Kaeufer*in fuer den gesamten Warenkorb bezahlt)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $fmt($order->commission) }}</td>
                            <td>{{ $fmt($order->vendor_total) }}</td>
                            <td>{{ $fmt($order->total) }}</td>
                            <td>{{ $fmt($buyerTotal) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="order-section" id="seller-print-block">
            <h2 class="order-section-title">
                Gutschrift der Hersteller*in
                <button type="button" class="print-btn" onclick="window.print()">Drucken</button>
            </h2>

            <div class="order-grid">
                <div>
                    <p class="label">Hersteller*in</p>
                    <p>
                        {{ $order->seller_info->f_name ?? $order->vendor?->first_name ?? $order->vendor?->name }}
                        {{ $order->seller_info->l_name ?? $order->vendor?->last_name }}<br>
                        {{ $order->seller_info->street ?? $order->vendor?->address?->street ?? $order->vendor?->verification?->street }}
                        {{ $order->seller_info->house_no ?? $order->vendor?->address?->house_no ?? $order->vendor?->verification?->house_no }}<br>
                        {{ $order->seller_info->zip ?? $order->vendor?->address?->zip ?? $order->vendor?->verification?->zip }}
                        {{ $order->seller_info->federal_state ?? $order->vendor?->address?->federal_state ?? $order->vendor?->verification?->city }}<br>
                        {{ $order->seller_info->email ?? $order->vendor?->email }}
                    </p>
                    @if (!empty($order->vendor?->vat))
                        <p>Steuernummer: {{ $order->vendor->vat }}</p>
                    @endif
                </div>
                <div>
                    <p class="label">Anbieterinformation</p>
                    <p>
                        Frau Kruner<br>
                        Inh. Frau Kathleen Krueger<br>
                        Schoenhauser Allee 163<br>
                        10435 Berlin<br>
                        USt.-Ident.-Nr.: DE419009695
                    </p>
                    <p>
                        Gutschrift-Nr.: FK{{ $order->created_at?->year }}-{{ $order->id }}-{{ $order->vendor?->id }}<br>
                        Gutschrift-Datum: {{ $order->created_at?->format('d.m.Y') }}
                    </p>
                </div>
            </div>

            <div class="order-table-wrap">
                <p class="label">Details</p>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Produktname</th>
                            <th>Versandkosten</th>
                            <th>Veredelungen</th>
                            <th>Zusatzoptionen</th>
                            <th>Basispreis</th>
                            <th>Gesamt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $order->product_name ?? $order->product?->name ?? '-' }}</td>
                            <td>{{ $fmt($order->shipping_cost) }}</td>
                            <td>{{ $listText($order->finishings) }}</td>
                            <td>{{ $listText($order->addition) }}</td>
                            <td>{{ $fmt($order->subtotal) }}</td>
                            <td>{{ $fmt($order->vendor_total) }}</td>
                        </tr>
                    </tbody>
                </table>
                <p class="muted-note">
                    {{ isset($order->seller_info->is_pay_vat) && (int) $order->seller_info->is_pay_vat === 1 ? 'Alle Preise sind inklusive der gesetzlichen Umsatzsteuer.' : 'Gemaess § 19 UStG enthaelt der o.g. Rechnungsbetrag keine Umsatzsteuer.' }}
                </p>
            </div>
        </section>

        <section class="order-section" id="buyer-print-block">
            <h2 class="order-section-title">
                Rechnung fuer den Kaeufer
                <button type="button" class="print-btn" onclick="window.print()">Drucken</button>
            </h2>

            <div class="order-grid">
                <div>
                    <p class="label">Kaeufer</p>
                    <p>
                        {{ $order->first_name }} {{ $order->last_name }}<br>
                        {{ $order->street }} {{ $order->house_no }}<br>
                        {{ $order->zip }} {{ $order->federal_state }}<br>
                        @if ($order->po_box)
                            Postfach: {{ $order->po_box }}<br>
                        @endif
                        {{ $order->user?->email ?? $order->email }}
                    </p>
                </div>
                <div>
                    <p class="label">Anbieterinformation</p>
                    <p>
                        Frau Kruner<br>
                        Inh. Frau Kathleen Krueger<br>
                        Schoenhauser Allee 163<br>
                        10435 Berlin<br>
                        USt.-Ident.-Nr.: DE419009695
                    </p>
                    <p>
                        Rechnungs-Nr.: FK{{ $order->created_at?->year }}-{{ $order->id }}<br>
                        Rechnungs-Datum: {{ $order->created_at?->format('d.m.Y') }}
                    </p>
                </div>
            </div>

            <div class="order-table-wrap">
                <p class="label">Details</p>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Produktname</th>
                            <th>Veredelungen</th>
                            <th>Zusatzoptionen</th>
                            <th>Gesamt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $order->product_name ?? $order->product?->name ?? '-' }}</td>
                            <td>{{ $listText($order->finishings) }}</td>
                            <td>{{ $listText($order->addition) }}</td>
                            <td>{{ $fmt($order->total) }}</td>
                        </tr>
                    </tbody>
                </table>
                <p class="muted-note">Umsatzsteuer wird gemaess § 25a UStG nicht ausgewiesen.</p>
            </div>
        </section>
    </div>

</x-filament-panels::page>
