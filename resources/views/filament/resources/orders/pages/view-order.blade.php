<x-filament-panels::page>
    @php
        // Der Datensatz ist der Kopf der Bestellung. Die Artikel hängen als
        // Positionen daran, je einer pro Verkäuferin – und weil jede Verkäuferin
        // selbst abrechnet, gibt es Gutschrift und Rechnung je Position.
        //
        // Eine Bestellung ohne Positionen (Altdatensatz) ist ihre eigene einzige
        // Position; dann sieht die Seite aus wie vorher.
        $order = $this->getRecord();

        $positions = $order->childrens->isNotEmpty() ? $order->childrens : collect([$order]);

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

        // total des Bestellkopfs kommt aus Cart::getTotal() und ist bereits um den
        // Rabatt reduziert – hier darf er nicht erneut abgezogen werden.
        $buyerTotal = $order->total;

        // Storniert wird je Position (HomeController::orderCancel()). Der
        // Stornostand eines Belegs ergibt sich deshalb aus seinen Positionen und
        // nicht aus dem status des Bestellkopfs – der bleibt beim Storno einer
        // einzelnen Position stehen.
        $stornierte = $positions->filter(fn ($p) => (int) ($p->status ?? 0) === 3);
        $allesStorniert = $stornierte->count() === $positions->count();
        $teilweiseStorniert = $stornierte->isNotEmpty() && ! $allesStorniert;
    @endphp

    <x-invoice.document-styles />

    <div class="invoice-document">
        {{-- Der Kopf der Bestellung: das, was die Kundin bezahlt hat. --}}
        <section class="invoice-document__section" id="order-print-block">
            <x-invoice.header
                title="Bestellung"
                :subtitle="$order->orderNumber() . ' · ' . $order->created_at?->format('d.m.Y')"
            />

            @if ($allesStorniert)
                <x-invoice.cancelled-banner label="Bestellung storniert" />
            @elseif ($teilweiseStorniert)
                <x-invoice.cancelled-banner
                    scope="teilweise"
                    :label="$stornierte->count() . ' von ' . $positions->count() . ' Artikeln storniert'"
                />
            @endif

            <div class="invoice-document__section-heading no-print">
                <h2>Übersicht</h2>
                <button type="button" class="invoice-document__print-btn" onclick="printInvoiceSection('order-print-block')">Drucken</button>
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
                    <p class="invoice-document__label">Zahlung</p>
                    <p class="invoice-document__meta">
                        Bestellnummer: {{ $order->orderNumber() }}<br>
                        Bezahlt: {{ $fmt($buyerTotal) }}<br>
                        @if (filled($order->discount_code) && (float) $order->discount > 0)
                            Zwischensumme: {{ $fmt($order->subtotal) }}<br>
                            Gutschein {{ $order->discount_code }}: −{{ $fmt($order->discount) }}<br>
                        @endif
                        Zahlungsart: {{ $order->payment_gateway ?: '-' }}<br>
                        @if (filled($order->payment_id))
                            Transaktion: {{ $order->payment_id }}<br>
                        @endif
                        Artikel: {{ $positions->count() }}
                    </p>
                </div>
            </div>

            @if (filled($order->message))
                <div class="invoice-document__table-wrap">
                    <p class="invoice-document__label">Mitteilung des Käufers</p>
                    <p class="invoice-document__address">{{ $order->message }}</p>
                </div>
            @endif

            <div class="invoice-document__table-wrap">
                <p class="invoice-document__label">Artikel dieser Bestellung</p>
                <table class="invoice-document__table">
                    <thead>
                        <tr>
                            <th>Artikel</th>
                            <th>Verkäuferin</th>
                            <th>Versand</th>
                            <th>Status</th>
                            <th>Betrag</th>
                            <th>Verkäuferin bekommt</th>
                            <th>Plattformgebühr</th>
                            <th class="no-print"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($positions as $index => $position)
                            @php
                                // 'total' ist der Bruttowert der Position, 'discount'
                                // ihr Anteil am Gutschein – genauso rechnet die Rechnung.
                                $positionsbetrag = max(0, (float) $position->total - (float) $position->discount);
                                $verkaeuferin = trim(($position->vendor?->name ?? '') . ' ' . ($position->vendor?->last_name ?? ''));
                                $artikelname = $position->product_name ?? $position->product?->name ?? '';
                                $storniert = (int) ($position->status ?? 0) === 3;
                            @endphp
                            <tr class="{{ $storniert ? 'is-cancelled' : '' }}">
                                <td>
                                    {{ $artikelname !== '' ? $artikelname : '-' }}
                                    <br><small style="opacity:.6">Position {{ $index + 1 }} · Beleg-Nr. {{ $position->id }}</small>
                                </td>
                                <td>{{ $verkaeuferin !== '' ? $verkaeuferin : '-' }}</td>
                                <td>
                                    @if (filled($position->shipping_date))
                                        {{ $position->shipping_date->format('d.m.Y') }}
                                        @if (filled($position->shipping_method))
                                            <br><small style="opacity:.6">{{ $position->shipping_method }}</small>
                                        @endif
                                        @if (filled($position->tracking_Id))
                                            <br><small style="opacity:.6">{{ $position->tracking_Id }}</small>
                                        @endif
                                    @else
                                        offen
                                    @endif
                                </td>
                                <td>{{ $storniert ? 'storniert' : 'aktiv' }}</td>
                                <td>{{ $fmt($positionsbetrag) }}</td>
                                <td>{{ $fmt($position->vendor_total) }}</td>
                                <td>{{ $fmt($position->commission) }}</td>
                                <td class="no-print">
                                    {{-- Storniert wird je Position: Es betrifft eine
                                         Verkäuferin, die nicht geliefert hat, und
                                         benachrichtigt genau sie. --}}
                                    @unless ($storniert)
                                        <a
                                            class="invoice-document__print-btn"
                                            href="{{ route('admin.order.cancel', $position) }}"
                                            onclick="return confirm({{ Illuminate\Support\Js::from('Position „' . $artikelname . '“ dieser Bestellung wirklich stornieren? Käuferin und Verkäuferin werden benachrichtigt.') }});"
                                        >Stornieren</a>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align:right"><strong>Summe</strong></td>
                            <td><strong>{{ $fmt($positions->sum(fn ($p) => max(0, (float) $p->total - (float) $p->discount))) }}</strong></td>
                            <td><strong>{{ $fmt($positions->sum('vendor_total')) }}</strong></td>
                            <td><strong>{{ $fmt($positions->sum('commission')) }}</strong></td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
                @if ($positions->count() > 1)
                    <p class="invoice-document__note">
                        Jede Position kommt von einer anderen Verkäuferin und wird einzeln
                        abgerechnet und versendet. Bezahlt hat die Kundin den Gesamtbetrag
                        einmal, unter der Bestellnummer {{ $order->orderNumber() }}.
                    </p>
                @endif
            </div>
        </section>

        {{-- Die Rechnung des Käufers: EIN Beleg für die ganze Bestellung, mit
             allen Artikeln darauf. Rechnungsstellerin ist Frau Kruner, und
             bezahlt wurde die Bestellung als Ganzes – deshalb trägt die Rechnung
             die Bestellnummer. Die Herstellerinnen rechnen getrennt ab, jede über
             ihre Gutschrift weiter unten. --}}
        <section class="invoice-document__section" id="buyer-print-block">
            <x-invoice.header
                title="Rechnung für den Käufer"
                :subtitle="'Rechnungs-Nr. ' . $order->invoiceNumber() . ' · ' . $order->created_at?->format('d.m.Y')"
            />

            @if ($allesStorniert)
                <x-invoice.cancelled-banner label="Rechnung storniert" />
            @elseif ($teilweiseStorniert)
                <x-invoice.cancelled-banner
                    scope="teilweise"
                    :label="'Einzelne Positionen storniert – ' . $stornierte->count() . ' von ' . $positions->count()"
                />
            @endif

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
                        Rechnungs-Nr.: {{ $order->invoiceNumber() }}<br>
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
                            <th>Tragedauer</th>
                            <th>Gesamt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($positions as $position)
                            <tr class="{{ (int) ($position->status ?? 0) === 3 ? 'is-cancelled' : '' }}">
                                <td>
                                    {{ $position->product_name ?? $position->product?->name ?? '-' }}
                                    @if ((int) ($position->status ?? 0) === 3)
                                        <br><small>storniert</small>
                                    @endif
                                </td>
                                <td>{{ $listText($position->finishings) }}</td>
                                <td>{{ $listText($position->addition) }}</td>
                                <td>{{ $listText($position->wearing_time) }}</td>
                                <td>{{ $fmt($position->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if ((float) $order->discount > 0)
                            <tr>
                                <td colspan="4" style="text-align:right">Zwischensumme</td>
                                <td>{{ $fmt($positions->sum('total')) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align:right">
                                    Gutschein{{ filled($order->discount_code) ? ' ' . $order->discount_code : '' }}
                                </td>
                                <td>−{{ $fmt($order->discount) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="4" style="text-align:right"><strong>Gesamtbetrag</strong></td>
                            <td><strong>{{ $fmt($buyerTotal) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                <p class="invoice-document__note">Umsatzsteuer wird gemäß § 25a UStG nicht ausgewiesen.</p>
            </div>

            <p class="invoice-document__footer">
                Frau Kruner · Schönhauser Allee 163 · 10435 Berlin · fraukruner.de
            </p>
        </section>

        {{-- Ab hier je Position die Gutschrift der Herstellerin: Jede rechnet
             einzeln ab. Der Druckblock trägt die ID der Position, damit jede
             Gutschrift für sich gedruckt werden kann.

             Die früheren Abschnitte „Bestelldetails · Position n von m“ sind
             entfallen: Sie wiederholten, was in der Übersicht und auf der
             Gutschrift steht. Die Plattformgebühr, die es nur dort gab, steht
             jetzt in der Übersicht. --}}
        @foreach ($positions as $index => $position)
            @php
                // Zwei Gutschriften unterscheiden sich durch die Herstellerin, nicht
                // durch eine Positionsnummer.
                $herstellerin = trim(($position->vendor?->name ?? '') . ' ' . ($position->vendor?->last_name ?? ''));
            @endphp

            <section class="invoice-document__section" id="seller-print-block-{{ $position->id }}">
                <x-invoice.header
                    title="Gutschrift der Hersteller*in"
                    :subtitle="'Gutschrift-Nr. ' . $position->gutschriftNumber() . ' · ' . $position->created_at?->format('d.m.Y')"
                />

                @if ((int) ($position->status ?? 0) === 3)
                    <x-invoice.cancelled-banner label="Gutschrift storniert" />
                @endif

                <div class="invoice-document__section-heading no-print">
                    <h2>Gutschrift{{ $herstellerin !== '' ? ' · ' . $herstellerin : '' }}</h2>
                    <button type="button" class="invoice-document__print-btn" onclick="printInvoiceSection('seller-print-block-{{ $position->id }}')">Drucken</button>
                </div>

                <div class="invoice-document__grid">
                    <div>
                        <p class="invoice-document__label">Hersteller*in</p>
                        <p class="invoice-document__address">
                            {{ \App\Order::firstFilled($position->seller_info->f_name ?? null, $position->vendor?->first_name, $position->vendor?->name) }}
                            {{ \App\Order::firstFilled($position->seller_info->l_name ?? null, $position->vendor?->last_name) }}<br>
                            {{ \App\Order::firstFilled($position->seller_info->street ?? null, $position->vendor?->address?->street, $position->vendor?->verification?->street) }}
                            {{ \App\Order::firstFilled($position->seller_info->house_no ?? null, $position->vendor?->address?->house_no, $position->vendor?->verification?->house_no) }}<br>
                            {{ \App\Order::firstFilled($position->seller_info->zip ?? null, $position->vendor?->address?->zip, $position->vendor?->verification?->zip) }}
                            {{ \App\Order::firstFilled($position->seller_info->federal_state ?? null, $position->vendor?->address?->federal_state, $position->vendor?->verification?->city) }}<br>
                            {{ \App\Order::firstFilled($position->seller_info->email ?? null, $position->vendor?->email) }}
                        </p>
                        @if (!empty($position->vendor?->vat))
                            <p class="invoice-document__meta">Steuernummer: {{ $position->vendor->vat }}</p>
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
                            Gutschrift-Nr.: {{ $position->gutschriftNumber() }}<br>
                            Gutschrift-Datum: {{ $position->created_at?->format('d.m.Y') }}<br>
                            zur Bestellung: {{ $order->orderNumber() }}
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
                                <td>{{ $position->product_name ?? $position->product?->name ?? '-' }}</td>
                                <td>{{ $fmt($position->shipping_cost) }}</td>
                                <td>{{ $listText($position->finishings) }}</td>
                                <td>{{ $listText($position->addition) }}</td>
                                <td>{{ $listText($position->wearing_time) }}</td>
                                <td>{{ $fmt($position->subtotal) }}</td>
                                <td>{{ $fmt($position->vendor_total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="invoice-document__note">
                        {{ isset($position->seller_info->is_pay_vat) && (int) $position->seller_info->is_pay_vat === 1 ? 'Alle Preise sind inklusive der gesetzlichen Umsatzsteuer.' : 'Gemäß § 19 UStG enthält der o.g. Rechnungsbetrag keine Umsatzsteuer.' }}
                    </p>
                </div>

                <p class="invoice-document__footer">
                    Frau Kruner · Schönhauser Allee 163 · 10435 Berlin · fraukruner.de
                </p>
            </section>

            </section>
        @endforeach
    </div>
</x-filament-panels::page>
