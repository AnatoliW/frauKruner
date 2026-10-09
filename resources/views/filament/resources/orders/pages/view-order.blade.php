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
                                    <br><small style="opacity:.6">Position {{ $index + 1 }} · Beleg-Nr. {{ $position->invoiceNumber() }}</small>
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

        {{-- Die Rechnungen des Käufers: ein Beleg je Artikel, jeder mit eigener
             Belegnummer. Jeder Artikel kommt von einer eigenen Herstellerin, die
             einzeln abrechnet – ihre Gutschrift weiter unten steht auf derselben
             Nummer und hängt nur ihre Nutzer-ID an.

             Bezahlt wurde die Bestellung als Ganzes. Deren Nummer steht auf
             jedem Beleg als „zur Bestellung“, damit sich eine Rechnung dem
             Zahlungseingang auf dem Kontoauszug zuordnen lässt. --}}
        @foreach ($positions as $position)
            @php
                $positionStorniert = (int) ($position->status ?? 0) === 3;
                $positionRabatt = (float) ($position->discount ?? 0);
                $positionBrutto = (float) ($position->total ?? 0);
                $stelle = $position->positionInOrder();
            @endphp

            <section class="invoice-document__section" id="buyer-print-block-{{ $position->id }}">
                <x-invoice.header
                    title="Rechnung für den Käufer"
                    :subtitle="'Rechnungs-Nr. ' . $position->invoiceNumber() . ' · ' . $position->created_at?->format('d.m.Y')"
                />

                @if ($positionStorniert)
                    <x-invoice.cancelled-banner label="Rechnung storniert" />
                @endif

                <div class="invoice-document__section-heading no-print">
                    <h2>
                        Käufer
                        @if ($stelle)
                            · Artikel {{ $stelle[0] }} von {{ $stelle[1] }}
                        @endif
                    </h2>
                    <button type="button" class="invoice-document__print-btn" onclick="printInvoiceSection('buyer-print-block-{{ $position->id }}')">Drucken</button>
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
                            Rechnungs-Nr.: {{ $position->invoiceNumber() }}<br>
                            Rechnungs-Datum: {{ $position->created_at?->format('d.m.Y') }}<br>
                            zur Bestellung: {{ $order->orderNumber() }}
                        </p>
                    </div>
                </div>

                <div class="invoice-document__table-wrap">
                    <p class="invoice-document__label">Position</p>
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
                            <tr class="{{ $positionStorniert ? 'is-cancelled' : '' }}">
                                <td>
                                    {{ $position->product_name ?? $position->product?->name ?? '-' }}
                                    @if ($positionStorniert)
                                        <br><small>storniert</small>
                                    @endif
                                </td>
                                <td>{{ $listText($position->finishings) }}</td>
                                <td>{{ $listText($position->addition) }}</td>
                                <td>{{ $listText($position->wearing_time) }}</td>
                                <td>{{ $fmt($positionBrutto) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            @if ($positionRabatt > 0)
                                {{-- Der Gutschein der Bestellung, anteilig auf diese
                                     Position. Der volle Rabatt steht im Kopf. --}}
                                <tr>
                                    <td colspan="4" style="text-align:right">Zwischensumme</td>
                                    <td>{{ $fmt($positionBrutto) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="text-align:right">
                                        Gutschein{{ filled($order->discount_code) ? ' ' . $order->discount_code : '' }}
                                    </td>
                                    <td>−{{ $fmt($positionRabatt) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4" style="text-align:right"><strong>Gesamtbetrag</strong></td>
                                <td><strong>{{ $fmt(max(0, $positionBrutto - $positionRabatt)) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    <p class="invoice-document__note">Umsatzsteuer wird gemäß § 25a UStG nicht ausgewiesen.</p>
                    @if ($stelle)
                        <p class="invoice-document__note">
                            Artikel {{ $stelle[0] }} von {{ $stelle[1] }} der Bestellung
                            {{ $order->orderNumber() }}. Für die weiteren Artikel gibt es
                            je eine eigene Rechnung mit eigener Nummer.
                        </p>
                    @endif
                </div>

                <p class="invoice-document__footer">
                    Frau Kruner · Schönhauser Allee 163 · 10435 Berlin · fraukruner.de
                </p>
            </section>
        @endforeach

        {{-- Ab hier je Position die Gutschrift der Herstellerin: Jede rechnet
             einzeln ab. Der Druckblock trägt die ID der Position, damit jede
             Gutschrift für sich gedruckt werden kann.

             Die früheren Abschnitte „Bestelldetails · Position n von m“ sind
             entfallen: Sie wiederholten, was in der Übersicht und auf der
             Gutschrift steht. Die Plattformgebühr, die es nur dort gab, steht
             jetzt in der Übersicht. --}}
        @foreach ($positions as $index => $position)
            @php
                // Zwei Gutschriften unterscheiden sich durch die Herstellerin – und,
                // wenn beide Artikel von derselben Frau kommen, nur noch durch den
                // Artikel. Beides steht deshalb über der Gutschrift, damit die aus
                // der Auszahlungsliste verlinkte auf Anhieb zu erkennen ist.
                $herstellerin = trim(($position->vendor?->name ?? '') . ' ' . ($position->vendor?->last_name ?? ''));
                $artikel = (string) ($position->product_name ?? $position->product?->name ?? '');
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
                    <h2>Gutschrift{{ $herstellerin !== '' ? ' · ' . $herstellerin : '' }}{{ $artikel !== '' ? ' · ' . $artikel : '' }}</h2>
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
        @endforeach
    </div>

    {{-- Aus der Auszahlungsliste wird eine einzelne Gutschrift direkt verlinkt
         (…/orders/123#seller-print-block-456). Der Browser springt dort nicht
         von allein hin: Die Seite kommt von Livewire, und wenn der Anker
         ausgewertet wird, steht der Abschnitt noch nicht im Dokument.

         Deshalb hier selbst scrollen – und den Abschnitt kurz hervorheben, denn
         bei einer Bestellung mit mehreren Artikeln sehen die Gutschriften
         untereinander gleich aus. Ohne die Markierung bleibt offen, welche davon
         zu der angeklickten Auszahlung gehört. --}}
    <script>
        (function () {
            // Einmal definieren: Bei einer Livewire-Navigation wird dieses Skript
            // erneut ausgeführt, und ein zweiter Listener am document würde jedes
            // Mal mitlaufen.
            if (typeof window.fkScrollToGutschrift !== 'function') {
                window.fkScrollToGutschrift = function () {
                    const hash = window.location.hash;

                    // Verlinkt werden Gutschrift und Rechnung einer Position.
                    if (! hash || ! /-print-block-\d+$/.test(hash)) {
                        return;
                    }

                    const section = document.getElementById(hash.slice(1));

                    if (! section) {
                        return;
                    }

                    section.scrollIntoView({ behavior: 'smooth', block: 'start' });

                    section.classList.add('invoice-document__section--highlight');

                    // Die Markierung wieder abräumen: Sie soll den Weg zeigen,
                    // nicht dauerhaft eine Gutschrift anders aussehen lassen.
                    window.setTimeout(function () {
                        section.classList.remove('invoice-document__section--highlight');
                    }, 2600);
                };

                document.addEventListener('livewire:navigated', function () {
                    window.requestAnimationFrame(window.fkScrollToGutschrift);
                });
            }

            // Direkter Aufruf der Seite (neuer Tab, eingefügter Link).
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    window.requestAnimationFrame(window.fkScrollToGutschrift);
                });
            } else {
                window.requestAnimationFrame(window.fkScrollToGutschrift);
            }
        })();
    </script>
</x-filament-panels::page>
