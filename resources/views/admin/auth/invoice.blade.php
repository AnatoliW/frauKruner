@extends('layouts.app')
@if (Auth()->user()->role_id == 3)
    @section('title', 'Gutschrift')
@endif
@if (Auth()->user()->role_id == 2)
    @section('title', 'Rechnung')
@endif
@section('content')

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="">
                    <div class="no-print">
                        <button onclick="window.print()" class="btn btn-primary ms-3"><i class="fa fa-print"></i>
                            Drucken</button>
                    </div>


                    <div class="card-body {{ $order->status == 3 ? 'storniert' : '' }}" id="printableArea"
                        style="overflow-x:auto;">

                        @if (Auth()->user()->role_id == 3)
                            @if ($order->status == 3)
                                <h3 style="color:red">GUTSCHRIFT WURDE STORNIERT!</h3>
                            @endif
                        @endif
                        @if (Auth()->user()->role_id == 2)
                            @if ($order->status == 3)
                                <h3 style="color:red">RECHNUNG WURDE STORNIERT!</h3>
                            @endif
                        @endif

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    @if ($order)
                                        <div class="card-body">

                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <p><b>Kundeninformation</b></p>
                                                    @if (Auth()->user()->role_id == 3)
                                                        <p>
                                                         

                                                            {{ $order->seller_info->f_name ?? ($order->vendor->first_name ?? ($order->vendor->name ?? ($order->vendor->verification->name ?? null))) }}
                                                            {{ $order->seller_info->l_name ?? ($order->vendor->last_name ?? ($order->vendor->last_name ?? ($order->vendor->verification->last_name ?? null))) }}<br>
                                                            {{ $order->seller_info->street ?? ($order->vendor->address->street ?? ($order->vendor->verification->street ?? null)) }}
                                                            {{ $order->seller_info->house_no ?? ($order->vendor->address->house_no ?? ($order->vendor->verification->house_no ?? null)) }}<br>
                                                            {{ !empty($order->seller_info->zip) ? $order->seller_info->zip : ($order->vendor->address->zip ?? $order->vendor->verification->zip ?? null) }}
                        
                                                            {{ $order->seller_info->federal_state ?? ($order->vendor->address->federal_state ?? ($order->vendor->verification->city ?? null)) }}<br>
                 

                                                        </p>
                                                        @if (isset($order->vendor) && !is_null($order->vendor->vat) && $order->vendor->vat !== '')
                                                            <p>Steuernummer: {{ $order->vendor->vat }} </p>
                                                        @endif
                                                    @endif

                                                    @if (Auth()->user()->role_id == 2)
                                                        <p>
                                                            {{ $order->first_name }} {{ $order->last_name }}<br>
                                                            {{ $order->street }} {{ $order->house_no }}<br>
                                                            {{ $order->zip }} {{ $order->federal_state }}
                                                        </p>
                                                    @endif
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
                                                @if (Auth()->user()->role_id == 3)
                                                    <div class="col-12 col-md-6">
                                                        <p>Gutschrift-Nr.:
                                                            FK{{ $order->created_at->year }}-{{ $order->id }}-{{ $order->vendor->id }}<br>
                                                            Gutschrift-Datum:
                                                            {{ $order->created_at->format('d. M. Y') }}<br><br>
                                                        </p>
                                                    </div>
                                                @endif

                                                @if (Auth()->user()->role_id == 2)
                                                    <div class="col-12 col-md-6">
                                                        <p>Rechnungs-Nr.:
                                                            FK{{ $order->created_at->year }}-{{ $order->id }}<br>
                                                            Rechnungs-Datum:
                                                            {{ $order->created_at->format('d. M. Y') }}<br><br>
                                                        </p>
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
                                            @if (Auth()->user()->role_id == 3)
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
                                            @endif
                                            @if (Auth()->user()->role_id == 2)
                                                <table class="table table-hover no-footer">
                                                    <thead>
                                                        <tr>
                                                            <th>Produktname</th>
                                                            @if (!empty($order->finishings))
                                                                <th>Veredelungen</th>
                                                            @endif
                                                            @if (!empty($order->addition))
                                                                <th>Zusatzoptionen</th>
                                                            @endif
                                                            @if (!empty($order->wearing_time))
                                                                <th>Tragedauer</th>
                                                            @endif
                                                            @if ($order->discount > 0)
                                                                <th>Gutschein-Wert</th>
                                                            @endif

                                                            {{-- @if (isset($order->seller_info->is_pay_vat) && $order->seller_info->is_pay_vat == 1)
                                                                <th>MwSt.</th>
                                                            @endif --}}
                                                            <th>Gesamt</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>{{ $order->product->name }}</td>
                                                            @if ($order->finishings)
                                                                <td>
                                                                    <div>
                                                                        @foreach ($order->finishings as $data)
                                                                            {{ strstr($data, '-', true) }}
                                                                            @if (!$loop->last)
                                                                                ,
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                </td>
                                                            @endif
                                                            @if ($order->addition)
                                                                <td>
                                                                    <div>
                                                                        @foreach ($order->addition as $key => $data)
                                                                            {{ strstr($data, '-', true) }}
                                                                            @if (!$loop->last)
                                                                                ,
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                </td>
                                                            @endif
                                                            @if ($order->wearing_time)
                                                                <td>
                                                                    <div>

                                                                        @foreach ($order->wearing_time as $data)
                                                                            {{ strstr($data, '-', true) }}
                                                                            @if (!$loop->last)
                                                                                ,
                                                                            @endif
                                                                        @endforeach

                                                                    </div>
                                                                </td>
                                                            @endif

                                                            @if ($order->discount > 0)
                                                                <td>{{ Shop::price($order->discount) }}</td>
                                                            @endif
                                                            {{-- @if (isset($order->seller_info->is_pay_vat) && $order->seller_info->is_pay_vat == 0)
                                                    <td>{{ Shop::price($order->tax) }}</td>
                                                @endif --}}
                                                            <td>
                                                                @php
                                                                    $finalAmount = $order->total;
                                                                    if ($order->discount > 0) {
                                                                        $finalAmount -= $order->discount;
                                                                    }
                                                                @endphp
                                                                {{ Shop::price($finalAmount) }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                                @if (Auth()->user()->role_id == 2)
                                                    <div class="col-12 mt-5">
                                                        @if (($order->seller_info->vat_perchatage ?? 0) >= 1)
                                                            Umsatzsteuer wird gemäß § 25a UStG nicht ausgewiesen.
                                                        @else
                                                            Gemäß § 19 UStG enthält der o.g. Rechnungsbetrag keine Umsatzsteuer.
                                                        @endif
                                                    </div>
                                                @endif

                                            @endif
                                        </div>
                                        @if (Auth()->user()->role_id == 3)
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
