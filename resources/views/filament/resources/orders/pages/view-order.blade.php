<x-filament-panels::page>
    @php
        $order = $this->getRecord();

        $fmt = static function ($value): string {
            return sprintf('%.2f €', (float) ($value ?? 0));
        };

        $listText = static function ($items): string {
            if (is_array($items)) {
                $text = implode(', ', array_map(static fn ($item) => (string) $item, $items));

                return $text !== '' ? $text : '-';
            }

            if (is_object($items)) {
                $text = implode(', ', array_map(static fn ($item) => (string) $item, (array) $items));

                return $text !== '' ? $text : '-';
            }

            if (is_string($items) && trim($items) !== '') {
                return $items;
            }

            return '-';
        };

        $buyerTotal = $order->parent ? ($order->parent->total - ($order->discount > 0 ? $order->discount : 0)) : $order->total;
    @endphp

    <x-invoice.document-styles />

    <div class="invoice-document">
        <section class="invoice-document__section" id="admin-print-block">
            <x-invoice.header
                title="Admin-Infos"
                :subtitle="'Bestellung #' . $order->id . ' · ' . $order->created_at?->format('d.m.Y')"
            />

            <div class="invoice-document__section-heading no-print">
                <h2>Bestelldetails</h2>
                <button type="button" class="invoice-document__print-btn" onclick="printInvoiceSection('admin-print-block')">Drucken</button>
            </div>

            <div class="invoice-document__grid">
                <div>
                    <p class="invoice-document__label">Käufer</p>
                    <p class="invoice-document__address">
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

            <div class="invoice-document__table-wrap">
                <p class="invoice-document__label">Angaben</p>
                <table class="invoice-document__table">
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

            <div class="invoice-document__table-wrap">
                <table class="invoice-document__table">
                    <thead>
                        <tr>
                            <th>Plattformgebühr</th>
                            <th>Produktpreis</th>
                            <th>Preis inkl. Plattformgebühr</th>
                            <th>Gesamtbetrag (vom Käufer*in für den gesamten Warenkorb bezahlt)</th>
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

        <section class="invoice-document__section" id="seller-print-block">
            <x-invoice.header
                title="Gutschrift der Hersteller*in"
                :subtitle="'Gutschrift-Nr. FK' . $order->created_at?->year . '-' . $order->id . '-' . $order->vendor?->id . ' · ' . $order->created_at?->format('d.m.Y')"
            />

            <div class="invoice-document__section-heading no-print">
                <h2>Hersteller*in</h2>
                <button type="button" class="invoice-document__print-btn" onclick="printInvoiceSection('seller-print-block')">Drucken</button>
            </div>

            <div class="invoice-document__grid">
                <div>
                    <p class="invoice-document__label">Hersteller*in</p>
                    <p class="invoice-document__address">
                        {{ $order->seller_info->f_name ?? $order->vendor?->first_name ?? $order->vendor?->name }}
                        {{ $order->seller_info->l_name ?? $order->vendor?->last_name }}<br>
                        {{ $order->seller_info->street ?? $order->vendor?->address?->street ?? $order->vendor?->verification?->street }}
                        {{ $order->seller_info->house_no ?? $order->vendor?->address?->house_no ?? $order->vendor?->verification?->house_no }}<br>
                        {{ $order->seller_info->zip ?? $order->vendor?->address?->zip ?? $order->vendor?->verification?->zip }}
                        {{ $order->seller_info->federal_state ?? $order->vendor?->address?->federal_state ?? $order->vendor?->verification?->city }}<br>
                        {{ $order->seller_info->email ?? $order->vendor?->email }}
                    </p>
                    @if (!empty($order->vendor?->vat))
                        <p class="invoice-document__meta">Steuernummer: {{ $order->vendor->vat }}</p>
                    @endif
                </div>
                <div>
                    <p class="invoice-document__label">Anbieterinformation</p>
                    <p class="invoice-document__address">
                        Frau Kruner<br>
                        Inh. Frau Kathleen Krüger<br>
                        Schönhauser Allee 163<br>
                        10435 Berlin<br>
                        USt.-Ident.-Nr.: DE419009695
                    </p>
                    <p class="invoice-document__meta">
                        Gutschrift-Nr.: FK{{ $order->created_at?->year }}-{{ $order->id }}-{{ $order->vendor?->id }}<br>
                        Gutschrift-Datum: {{ $order->created_at?->format('d.m.Y') }}
                    </p>
                </div>
            </div>

            <div class="invoice-document__table-wrap">
                <p class="invoice-document__label">Positionen</p>
                <table class="invoice-document__table">
                    <thead>
                        <tr>
                            <th>Produktname</th>
                            <th>Versandkosten</th>
                            <th>Veredelungen</th>
                            <th>Zusatzoptionen</th>
                            <th>Tragedauer</th>
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
                            <td>{{ $listText($order->wearing_time) }}</td>
                            <td>{{ $fmt($order->subtotal) }}</td>
                            <td>{{ $fmt($order->vendor_total) }}</td>
                        </tr>
                    </tbody>
                </table>
                <p class="invoice-document__note">
                    {{ isset($order->seller_info->is_pay_vat) && (int) $order->seller_info->is_pay_vat === 1 ? 'Alle Preise sind inklusive der gesetzlichen Umsatzsteuer.' : 'Gemäß § 19 UStG enthält der o.g. Rechnungsbetrag keine Umsatzsteuer.' }}
                </p>
            </div>

            <p class="invoice-document__footer">
                Frau Kruner · Schönhauser Allee 163 · 10435 Berlin · fraukruner.de
            </p>
        </section>

        <section class="invoice-document__section" id="buyer-print-block">
            <x-invoice.header
                title="Rechnung für den Käufer"
                :subtitle="'Rechnungs-Nr. FK' . $order->created_at?->year . '-' . $order->id . ' · ' . $order->created_at?->format('d.m.Y')"
            />

            <div class="invoice-document__section-heading no-print">
                <h2>Käufer</h2>
                <button type="button" class="invoice-document__print-btn" onclick="printInvoiceSection('buyer-print-block')">Drucken</button>
            </div>

            <div class="invoice-document__grid">
                <div>
                    <p class="invoice-document__label">Käufer</p>
                    <p class="invoice-document__address">
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
                    <p class="invoice-document__label">Anbieterinformation</p>
                    <p class="invoice-document__address">
                        Frau Kruner<br>
                        Inh. Frau Kathleen Krüger<br>
                        Schönhauser Allee 163<br>
                        10435 Berlin<br>
                        USt.-Ident.-Nr.: DE419009695
                    </p>
                    <p class="invoice-document__meta">
                        Rechnungs-Nr.: FK{{ $order->created_at?->year }}-{{ $order->id }}<br>
                        Rechnungs-Datum: {{ $order->created_at?->format('d.m.Y') }}
                    </p>
                </div>
            </div>

            <div class="invoice-document__table-wrap">
                <p class="invoice-document__label">Positionen</p>
                <table class="invoice-document__table">
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
                <p class="invoice-document__note">Umsatzsteuer wird gemäß § 25a UStG nicht ausgewiesen.</p>
            </div>

            <p class="invoice-document__footer">
                Frau Kruner · Schönhauser Allee 163 · 10435 Berlin · fraukruner.de
            </p>
        </section>
    </div>
</x-filament-panels::page>
