<x-filament-panels::page>
    @php
        $boost = $this->getRecord();
        $payment = $boost->payment ?? $boost->payments->first();
        $tax = $payment?->tax ?? 0;

        $fmt = static function ($value): string {
            return sprintf('%.2f €', (float) ($value ?? 0));
        };

        $buyerFirstName = $boost->user_info->f_name ?? $boost->user?->name ?? '';
        $buyerLastName = $boost->user_info->l_name ?? $boost->user?->last_name ?? '';

        $invoiceNumber = $boost->invoice_number;
    @endphp

    <x-invoice.document-styles />

    <div class="invoice-document">
        <section class="invoice-document__section" id="boost-print-block">
            <x-invoice.header
                title="Rechnung"
                :subtitle="'Rechnungs-Nr. ' . $invoiceNumber . ' · ' . $boost->created_at->format('d.m.Y')"
            />

            <div class="invoice-document__section-heading no-print">
                <h2>Rechnungsdetails</h2>
                <button type="button" class="invoice-document__print-btn" onclick="printInvoiceSection('boost-print-block')">Drucken</button>
            </div>

            <div class="invoice-document__grid">
                <div>
                    <p class="invoice-document__label">Käufer</p>
                    <p class="invoice-document__address">
                        {{ trim($buyerFirstName . ' ' . $buyerLastName) }}<br>
                        {{ \App\Order::firstFilled($boost->user_info->street ?? null, $boost->user?->address?->street, $boost->user?->verification?->street) ?: '-' }} {{ \App\Order::firstFilled($boost->user_info->house_no ?? null, $boost->user?->address?->house_no, $boost->user?->verification?->house_no) }}<br>
                        {{ \App\Order::firstFilled($boost->user_info->zip ?? null, $boost->user?->address?->zip, $boost->user?->verification?->zip) ?: '-' }} {{ \App\Order::firstFilled($boost->user_info->federal_state ?? null, $boost->user?->address?->federal_state, $boost->user?->verification?->city) }}<br>
                        {{ \App\Order::firstFilled($boost->user_info->email ?? null, $boost->user?->email) ?: '-' }}
                    </p>

                    @if (!empty($boost->user_info->vat_number ?? null))
                        <p class="invoice-document__meta">Steuernummer: {{ $boost->user_info->vat_number }}</p>
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
                        Rechnungs-Nr.: {{ $invoiceNumber }}<br>
                        Rechnungs-Datum: {{ $boost->created_at->format('d.m.Y') }}
                    </p>
                </div>
            </div>

            <div class="invoice-document__table-wrap">
                <p class="invoice-document__label">Positionen</p>
                <table class="invoice-document__table">
                    <thead>
                        <tr>
                            <th>Produktname</th>
                            <th>Basispreis</th>
                            @if ($tax)
                                <th>MwSt</th>
                            @endif
                            <th>Gesamt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $boost->boostable?->name ?? $boost->boostable?->title ?? '-' }}</td>
                            <td>{{ $fmt($boost->base_price) }}</td>
                            @if ($tax)
                                <td>{{ $fmt($tax) }}</td>
                            @endif
                            <td>{{ $fmt($boost->price) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if (!$tax)
                <p class="invoice-document__note">Gemäß § 19 UStG enthält der o.g. Rechnungsbetrag keine Umsatzsteuer.</p>
            @endif

            <p class="invoice-document__footer">
                Frau Kruner · Schönhauser Allee 163 · 10435 Berlin · fraukruner.de
            </p>
        </section>
    </div>
</x-filament-panels::page>
