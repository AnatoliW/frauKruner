@extends('layouts.app')

@php
    // Drei Belege aus einer Vorlage:
    //
    //  - Die Herstellerin sieht die GUTSCHRIFT fuer ihre Position. Jede
    //    rechnet einzeln ab, deshalb gilt sie je Position.
    //  - Alle anderen (Kundin, Adminbereich) sehen die RECHNUNG, und deren Form
    //    haengt am Stichtag aus app.invoice_bundle_cutoff_date:
    //
    //      VOR dem Stichtag: eine EINZELRECHNUNG je Artikel, mit der
    //      Belegnummer in der Kopfzeile ("Rechnungs-Nr. FK2024-3552"). Genau so
    //      wurde sie damals ausgestellt, verschickt und archiviert.
    //
    //      AB dem Stichtag: eine SAMMELRECHNUNG ueber die ganze Bestellung. Sie
    //      traegt keine eigene Nummer, sondern nennt die BESTELLNUMMER als
    //      Bezug - die Nummer, die die Kundin bezahlt hat und die auf ihrem
    //      Kontoauszug steht - und fuehrt je Artikel dessen BELEGNUMMER auf.
    //
    // Die Belegnummer gilt in beiden Faellen je Position, und die Gutschrift
    // der Herstellerin baut darauf auf (Order::gutschriftNumber()). Sie steht
    // fest in der Spalte invoice_no, damit eine Formataenderung archivierte
    // Belege nicht umschreiben kann.
    //
    // $positions kommt aus HomeController::invoice(): fuer Gutschrift und
    // Einzelrechnung die eine Position, fuer die Sammelrechnung alle.
    $istGutschrift = (int) (Auth()->user()->role_id ?? 0) === 3;

    $positions = $positions ?? collect([$order]);

    // Vom Controller gesetzt; der Rueckfall deckt Aufrufe ohne das Flag ab.
    $istSammelrechnung = $istSammelrechnung ?? (! $istGutschrift && $order->usesBundledInvoice());

    // Spalten nur zeigen, wenn irgendeine Position etwas darin hat.
    $hatVeredelungen = $positions->contains(fn ($p) => !empty($p->finishings));
    $hatZusatzoptionen = $positions->contains(fn ($p) => !empty($p->addition));
    $hatTragedauer = $positions->contains(fn ($p) => !empty($p->wearing_time));

    // Zwischensumme der Rechnung: Bruttowerte der Positionen. Der Rabatt steht
    // am Kopf der Bestellung und wird genau einmal abgezogen.
    $stornierte = $positions->filter(fn ($p) => (int) ($p->status ?? 0) === 3);
    $allesStorniert = $stornierte->count() === $positions->count();
    $teilweiseStorniert = $stornierte->isNotEmpty() && ! $allesStorniert;

    $zwischensumme = (float) $positions->sum('total');
    $rabatt = (float) ($order->discount ?? 0);
    $gesamtbetrag = max(0, $zwischensumme - $rabatt);

    // Nur die Anfangsteile einer Auswahl anzeigen ("Rot-123" => "Rot").
    $auswahl = static function ($werte): string {
        if (empty($werte)) {
            return '-';
        }

        $teile = collect((array) $werte)
            ->map(fn ($wert) => strstr((string) $wert, '-', true) ?: (string) $wert)
            ->filter();

        return $teile->isEmpty() ? '-' : $teile->implode(', ');
    };
@endphp

@section('title', $istGutschrift ? 'Gutschrift' : 'Rechnung')
@section('content')

    <x-invoice.customer-header-styles />

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="">
                    <div class="no-print">
                        <button onclick="window.print()" class="btn btn-primary ms-3"><i class="fa fa-print"></i>
                            Drucken</button>
                    </div>


                    <div class="card-body {{ $allesStorniert ? 'storniert' : '' }}" id="printableArea"
                        style="overflow-x:auto;">

                        @if ($istGutschrift)
                            <x-invoice.header
                                title="Gutschrift"
                                :subtitle="'Gutschrift-Nr. ' . $order->gutschriftNumber() . ' · ' . $order->created_at->format('d.m.Y')"
                            />
                        @else
                            {{-- Sammelrechnung: kein eigenes "Rechnungs-Nr.", sie verweist auf
                                 die Bestellung; die Belegnummern stehen je Artikel in der
                                 Tabelle. Einzelrechnung: die Belegnummer dieser Position in
                                 der Kopfzeile, so wie vor dem Stichtag ausgestellt. --}}
                            <x-invoice.header
                                title="Rechnung"
                                :subtitle="($istSammelrechnung
                                    ? 'zur Bestellung ' . $order->orderNumber()
                                    : 'Rechnungs-Nr. ' . $order->invoiceNumber()) . ' · ' . $order->created_at->format('d.m.Y')"
                            />
                        @endif

                        @if ($allesStorniert)
                            <h3 style="color:red">{{ $istGutschrift ? 'GUTSCHRIFT' : 'RECHNUNG' }} WURDE STORNIERT!</h3>
                        @elseif ($teilweiseStorniert)
                            {{-- Teilstorno: Die Rechnung gilt weiter, aber nicht fuer
                                 jeden Artikel. Welcher betroffen ist, steht in der
                                 Positionstabelle. --}}
                            <h3 style="color:red">
                                {{ $stornierte->count() }} von {{ $positions->count() }}
                                Artikeln dieser Bestellung wurden storniert.
                            </h3>
                        @endif

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    @if ($order)
                                        <div class="card-body">

                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <p><b>Kundeninformation</b></p>
                                                    @if ($istGutschrift)
                                                        <p>
                                                         

                                                            {{ \App\Order::firstFilled($order->seller_info->f_name ?? null, $order->vendor?->first_name, $order->vendor?->name, $order->vendor?->verification?->name) }}
                                                            {{ \App\Order::firstFilled($order->seller_info->l_name ?? null, $order->vendor?->last_name, $order->vendor?->verification?->last_name) }}<br>
                                                            {{ \App\Order::firstFilled($order->seller_info->street ?? null, $order->vendor?->address?->street, $order->vendor?->verification?->street) }}
                                                            {{ \App\Order::firstFilled($order->seller_info->house_no ?? null, $order->vendor?->address?->house_no, $order->vendor?->verification?->house_no) }}<br>
                                                            {{ \App\Order::firstFilled($order->seller_info->zip ?? null, $order->vendor?->address?->zip, $order->vendor?->verification?->zip) }}

                                                            {{ \App\Order::firstFilled($order->seller_info->federal_state ?? null, $order->vendor?->address?->federal_state, $order->vendor?->verification?->city) }}<br>
                 

                                                        </p>
                                                        @if (isset($order->vendor) && !is_null($order->vendor->vat) && $order->vendor->vat !== '')
                                                            <p>Steuernummer: {{ $order->vendor->vat }} </p>
                                                        @endif
                                                    @endif

                                                    @unless ($istGutschrift)
                                                        <p>
                                                            {{ $order->first_name }} {{ $order->last_name }}<br>
                                                            {{ $order->street }} {{ $order->house_no }}<br>
                                                            {{ $order->zip }} {{ $order->federal_state }}
                                                        </p>
                                                    @endunless
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <p><b>Anbieterinformation</b></p>
                                                    <p>
                                                        Frau Kruner<br>
                                                        Inh. Frau Kathleen Krüger<br>
                                                        Schönhauser Allee 163<br>
                                                        10435 Berlin</p>
                                                    <p>USt.-Ident.-Nr.: DE419009695</p>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                </div>
                                                @if ($istGutschrift)
                                                    <div class="col-12 col-md-6">
                                                        <p>Gutschrift-Nr.:
                                                            {{ $order->gutschriftNumber() }}<br>
                                                            Gutschrift-Datum:
                                                            {{ $order->created_at->format('d. M. Y') }}<br>
                                                            {{-- Die Bestellung, aus der diese Position stammt. --}}
                                                            zur Bestellung:
                                                            {{ $order->orderNumber() }}<br><br>
                                                        </p>
                                                    </div>
                                                @else
                                                    <div class="col-12 col-md-6">
                                                        @if ($istSammelrechnung)
                                                            {{-- Die Bestellnummer als Bezug: Sie steht auf dem
                                                                 Kontoauszug der Kundin. Die Belegnummern der
                                                                 einzelnen Artikel stehen in der Tabelle. --}}
                                                            <p>zur Bestellung:
                                                                {{ $order->orderNumber() }}<br>
                                                                Rechnungs-Datum:
                                                                {{ $order->created_at->format('d. M. Y') }}<br><br>
                                                            </p>
                                                        @else
                                                            {{-- Einzelrechnung vor dem Stichtag: Die Belegnummer
                                                                 dieser Position ist die Rechnungsnummer. --}}
                                                            <p>Rechnungs-Nr.:
                                                                {{ $order->invoiceNumber() }}<br>
                                                                Rechnungs-Datum:
                                                                {{ $order->created_at->format('d. M. Y') }}<br><br>
                                                            </p>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                </div>
                            @else
                                <p>Aktualisieren Sie Ihre Benutzerinformationen</p>
                                @endif

                                <div class="card">
                                    <div class="card-body" style="overflow-x:auto;">
                                        <div class="col-sm-12">
                                            <h3 class="panel-title">Details</h3>
                                            @if ($istGutschrift)
                                                <table class="table table-hover no-footer">
                                                    <thead>
                                                        <tr role="row">

                                                            <th class="sorting" colspan="1" rowspan="1"
                                                                style="width: 15px;" tabindex="0">
                                                                Produktname
                                                            </th>
                                                            <th class="sorting" colspan="1" rowspan="1"
                                                                style="width: 15px;" tabindex="0">
                                                                Versandkosten
                                                            </th>
                                                            @if ($order->finishings)
                                                                <th class="sorting" colspan="1" rowspan="1"
                                                                    style="width: 15px;" tabindex="0">
                                                                    Veredelungen
                                                                </th>
                                                            @endif
                                                            @if ($order->addition)
                                                                <th class="sorting" colspan="1" rowspan="1"
                                                                    style="width: 15px;" tabindex="0">
                                                                    Zusatzoptionen
                                                                </th>
                                                            @endif
                                                            @if ($order->wearing_time)
                                                                <th class="sorting" colspan="1" rowspan="1"
                                                                    style="width: 15px;" tabindex="0">
                                                                    Tragedauer
                                                                </th>
                                                            @endif
                                                            <th class="sorting" colspan="1" rowspan="1"
                                                                style="width: 15px;" tabindex="0">
                                                                Basispreis
                                                            </th>
                                                            @if (isset($order->seller_info->is_pay_vat) && $order->seller_info->is_pay_vat == 1)
                                                                <th class="sorting" colspan="1" rowspan="1"
                                                                    style="width: 15px;" tabindex="0">
                                                                    Netto
                                                                </th>
                                                                <th class="sorting" colspan="1" rowspan="1"
                                                                    style="width: 15px;" tabindex="0">
                                                                {{--
                                                                    MwSt. ({{$order->seller_info->vat_perchatage}} %)
                                                                    --}}
       
                                                                    MwSt. (19 %)
                                                                    
                                                                </th>
                                                            @endif
                                                            <th class="sorting" colspan="1" rowspan="1"
                                                                style="width: 15px;" tabindex="0">
                                                                Gesamt
                                                            </th>

                                                            <!-- <th  class="sorting" colspan="1" rowspan="1" style="width: 15px;" tabindex="0">
                           Basic Price
                          </th> -->

                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        <tr class="even" role="row">

                                                            <td>
                                                                <div> {{ $order->product->name }}</div>
                                                            </td>
                                                            <!-- <td><div> {{ $order->category ? $order->category->name : '' }}</div></td> -->
                                                            <td>
                                                                <div> {{ Shop::price($order->shipping_cost) }}</div>
                                                            </td>
                                                            @if ($order->finishings)
                                                                <td>
                                                                    <div>
                                                                        @foreach ($order->finishings as $data)
                                                                            {{ $data }}
                                                                        @endforeach
                                                                    </div>
                                                                </td>
                                                            @endif
                                                            @if ($order->addition)
                                                                <td>
                                                                    <div>
                                                                        @foreach ($order->addition as $data)
                                                                            {{ $data }}
                                                                        @endforeach
                                                                    </div>
                                                                </td>
                                                            @endif
                                                            @if ($order->wearing_time)
                                                                <td>
                                                                    <div>
                                                                        @foreach ($order->wearing_time as $data)
                                                                            {{ $data }}
                                                                        @endforeach

                                                                    </div>
                                                                </td>
                                                            @endif
                                                            <td>
                                                                <div>{{ Shop::price($order->subtotal) }}</div>
                                                            </td>


                                                            @if (isset($order->seller_info->is_pay_vat) && $order->seller_info->is_pay_vat == 1)
                                                                <td>
                                                                    <div>
                                                                        {{--
                                                                        {{ Shop::price( $order->vendor_total / (($order->seller_info->vat_perchatage / 100) +1))  }}
                                                                        --}}
                                                                        {{ Shop::price( $order->vendor_total / ((19 / 100) +1))  }}
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        {{--
                                                                        {{ Shop::price(  $order->vendor_total - ($order->vendor_total / (($order->seller_info->vat_perchatage / 100) +1)) )  }}
                                                                        --}}
                                                                        {{ Shop::price(  $order->vendor_total - ($order->vendor_total / ((19 / 100) +1)) )  }}
                                                                    </div>
                                                                </td>
                                                            @endif
                                                            <td>
                                                                <div>{{ Shop::price($order->vendor_total) }}</div>
                                                            </td>


                                                        </tr>

                                                    </tbody>
                                                </table>
                                            @else
                                                {{-- Eine Rechnung fuer die ganze Bestellung: je Artikel eine Zeile,
                                                     darunter Zwischensumme, Gutschein und Gesamtbetrag. Vorher gab es
                                                     je Artikel eine eigene Rechnung mit eigener Nummer. --}}
                                                <table class="table table-hover no-footer">
                                                    <thead>
                                                        <tr>
                                                            <th>Produktname</th>
                                                            @if ($istSammelrechnung)
                                                                {{-- Die Belegnummer des Artikels: Darauf steht auch die
                                                                     Gutschrift seiner Herstellerin. Bei der
                                                                     Einzelrechnung steht sie schon in der Kopfzeile. --}}
                                                                <th>Beleg-Nr.</th>
                                                            @endif
                                                            @if ($hatVeredelungen)
                                                                <th>Veredelungen</th>
                                                            @endif
                                                            @if ($hatZusatzoptionen)
                                                                <th>Zusatzoptionen</th>
                                                            @endif
                                                            @if ($hatTragedauer)
                                                                <th>Tragedauer</th>
                                                            @endif
                                                            <th>Gesamt</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($positions as $position)
                                                            @php $positionStorniert = (int) ($position->status ?? 0) === 3; @endphp
                                                            <tr @class(['text-decoration-line-through' => $positionStorniert])>
                                                                <td>
                                                                    {{ $position->product_name ?? $position->product->name }}
                                                                    @if ($positionStorniert)
                                                                        <br><small style="color:red">storniert</small>
                                                                    @endif
                                                                </td>
                                                                @if ($istSammelrechnung)
                                                                    <td>{{ $position->invoiceNumber() }}</td>
                                                                @endif
                                                                @if ($hatVeredelungen)
                                                                    <td>{{ $auswahl($position->finishings) }}</td>
                                                                @endif
                                                                @if ($hatZusatzoptionen)
                                                                    <td>{{ $auswahl($position->addition) }}</td>
                                                                @endif
                                                                @if ($hatTragedauer)
                                                                    <td>{{ $auswahl($position->wearing_time) }}</td>
                                                                @endif
                                                                <td>{{ Shop::price($position->total) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        @php
                                                            // Spalten links vom Betrag, damit die Summen rechts ausgerichtet
                                                            // bleiben: Produktname und Beleg-Nr. plus die wahlweisen.
                                                            $leereSpalten = 1
                                                                + ($istSammelrechnung ? 1 : 0)
                                                                + ($hatVeredelungen ? 1 : 0)
                                                                + ($hatZusatzoptionen ? 1 : 0)
                                                                + ($hatTragedauer ? 1 : 0);
                                                        @endphp
                                                        @if ($rabatt > 0)
                                                            <tr>
                                                                <td colspan="{{ $leereSpalten }}" class="text-end">Zwischensumme</td>
                                                                <td>{{ Shop::price($zwischensumme) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="{{ $leereSpalten }}" class="text-end">
                                                                    {{ filled($order->discount_code) ? 'Gutschein ' . $order->discount_code : 'Gutschein' }}
                                                                </td>
                                                                <td>−{{ Shop::price($rabatt) }}</td>
                                                            </tr>
                                                        @endif
                                                        <tr>
                                                            <td colspan="{{ $leereSpalten }}" class="text-end"><b>Gesamtbetrag</b></td>
                                                            <td><b>{{ Shop::price($gesamtbetrag) }}</b></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>

                                                @if ($istSammelrechnung && $positions->count() > 1)
                                                    {{-- Warum mehrere Belegnummern auf einer Rechnung stehen. --}}
                                                    <p class="text-muted">
                                                        Diese Rechnung umfasst alle {{ $positions->count() }} Artikel der
                                                        Bestellung {{ $order->orderNumber() }}. Die Artikel kommen von
                                                        unterschiedlichen Herstellerinnen, werden einzeln versendet und
                                                        tragen deshalb je eine eigene Beleg-Nr.
                                                    </p>
                                                @endif

                                                <div class="col-12 mt-5">
                                                    @if (($order->seller_info->vat_perchatage ?? 0) >= 1)
                                                        Umsatzsteuer wird gemäß § 25a UStG nicht ausgewiesen.
                                                    @else
                                                        Gemäß § 19 UStG enthält der o.g. Rechnungsbetrag keine Umsatzsteuer.
                                                    @endif
                                                </div>

                                            @endif
                                        </div>
                                        @if ($istGutschrift)
                                            <div class="col-12 mt-5">
                                                <p>{{ isset($order->seller_info->is_pay_vat) && $order->seller_info->is_pay_vat == 1 ? 'Alle Preise sind inklusive der gesetzlichen Umsatzsteuer.' : 'Gemäß § 19 UStG enthält der o.g. Rechnungsbetrag keine Umsatzsteuer.' }}
                                                </p>
                                            </div>
                                        @endif


                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
