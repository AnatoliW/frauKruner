<x-dashboard type='seller' :title="$boost->package->name" :bread="[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard'),
    'Rechnungen' => route('seller.charges'),
]">

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="">
                    <div class="no-print">
                        <button onclick="window.print()" class="btn btn-primary ms-3"><i class="fa fa-print"></i>
                            Drucken</button>
                    </div>


                    <div class="card-body" id="charges">


                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">

                                    <div class="card-body" style="overflow-x:auto;">

                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <p><b>Kundeninformation</b></p>

                                                <p>
                                                    {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}<br>
                                                    {{ auth()->user()->address ? auth()->user()->address->street : '' }}
                                                    {{ auth()->user()->address ? auth()->user()->address->house_no : '' }}<br>
                                                    {{ auth()->user()->address ? auth()->user()->address->zip : '' }}
                                                    {{ auth()->user()->address ? auth()->user()->address->federal_state : '' }}
                                                    {{ auth()->user()->vat}}
                                                </p>
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



                                            <div class="col-12 col-md-6">
                                                @php
                                                    $cutoffDate = \Carbon\Carbon::parse(config('app.invoice_format_cutoff_date'));
                                                    $payment = $boost->payment ?? $boost->payments->first();
                                                    $useOldFormat = $boost->created_at->lt($cutoffDate) && $payment && $payment->payment_trnx_id;
                                                @endphp
                                                <p>Rechnungs-Nr:
                                                    @if ($useOldFormat)
                                                        FKB{{ $payment->payment_trnx_id }}<br>
                                                    @else
                                                        PFK-{{ $boost->created_at->format('Y') }}-{{ $boost->id }}<br>
                                                    @endif
                                                    Rechnungs-Datum:
                                                    {{ $boost->created_at->format('d.M.Y') }} <br><br>
                                                </p>
                                            </div>

                                        </div>

                                    </div>
                                </div>


                                <div class="card">
                                    <div class="card-body" style="overflow-x:auto;">
                                        <div class="col-sm-12">
                                            <h3 class="panel-title">Details</h3>
                                            @php
                                                $tax = $boost->payments->first()->tax;

                                            @endphp
                                            <table class="table table-hover no-footer">
                                                <thead>
                                                    <tr role="row">
                                                        <th class="sorting" colspan="1" rowspan="1"
                                                            style="width: 15px;" tabindex="0">
                                                            Produktname
                                                        </th>
                                                        <th class="sorting" colspan="1" rowspan="1"
                                                            style="width: 15px;" tabindex="0">
                                                            Basispreis
                                                        </th>
                                                        @if ($tax)
                                                            <th class="sorting" colspan="1" rowspan="1"
                                                                style="width: 15px;" tabindex="0">
                                                                MwSt. (19%)
                                                            </th>
                                                        @endif
                                                        <th class="sorting" colspan="1" rowspan="1"
                                                            style="width: 15px;" tabindex="0">
                                                            Gesamt
                                                        </th>

                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <tr class="even" role="row">
                                                        <td>
                                                            <div> {{ $boost->boostable->name ?? null }}</div>
                                                        </td>
                                                        <td>
                                                            <div>{{ Shop::price($boost->base_price) }}</div>
                                                        </td>

                                                        @if ($tax)
                                                            <th>
                                                                <div>{{ Shop::price($boost->tax) }}</div>
                                                            </th>
                                                        @endif
                                                        <td>
                                                            <div>{{ Shop::price($boost->price) }}</div>
                                                        </td>

                                                    </tr>

                                                </tbody>
                                            </table>
                                            @if (!$tax)
                                            <div class="col-12 mt-5">
                                                Gemäß § 19 UStG enthält die o.g. Kaufpreiszahlung keine Umsatzsteuer.
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
    </div>
</x-dashboard>
