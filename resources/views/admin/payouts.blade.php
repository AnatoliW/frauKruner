@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing') . ' ' .'Auszahlungen')

@section('page_header')

<h1 class="page-title" style="display: flex;align-items:center">
            <svg height="30" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
              </svg>
                   Auszahlungen
</h1>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                   
                        <div class="table-responsive">
                            <form action="" method="get">
                                  <div class="row " style="display:flex;justify-content:end;align-items:center">
                                    <div class="col-md-4">

                                        <input type="text" class="form-control" value="{{request()->search}}" name="search" placeholder="Suchen">
                                    </div>
                                    <div class="col-md-1">
                                        <button class="btn btn-primary" type="submit">Suchen</button>
                                    </div>
                                  </div>
                                   
                            </form>
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                    <tr>

                                        <th>
                                            Verkäufer/in
                                        </th>
                                        <th>
                                            Käufer/in - Produkt
                                        </th>
                                        <th>
                                            Bestell-ID
                                        </th>
                                        <th>
                                            Versandstatus
                                        </th>
                                        <th>
                                           Zahlungsinformationen
                                        </th>
                                        <th>
                                           Nachricht
                                        </th>
                                        <!-- <th>
                                            Auszahlung angefragt?
                                        </th> -->

                                        <th class="actions text-right dt-not-orderable">
                                            {{ __('voyager::generic.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($dataTypeContent as $data)
                                        <tr>
                                            <td class="middle">
                                                <br>
                                                @if (@$data->vendor)
                                                    {{ $data->vendor->name }}
                                                    {{ $data->vendor->last_name }}
                                                @else
                                                    <span class="text-danger">
                                                        Namen nicht gefunden
                                                    </span>
                                                @endif
                                                <br><br>
                                                @if(!empty($data->vendor->method))
                                                    <span style="font-weight: bold;">IBAN:</span>
                                                    <p >
                                                        {{ $data->vendor->method->iban }}
                                                    </p><br>
                                                    <!-- <span style="font-weight: bold;">BIC:</span>
                                                    <p>
                                                        {{ $data->vendor->method->bic }}
                                                    </p> -->
                                                    <br><br>
                                                @else
                                                    <span style="font-weight: bold;">Keine Bank hinterlegt</span> <br><br>
                                                @endif


                                            </td>

                                            <td  class="middle">
                                                {{ $data->first_name }} {{ $data->last_name }}<br>
                                                {{ $data->additional }} <br>
                                                {{ $data->street }} {{ $data->house_no }}<br>
                                                {{ $data->zip }} {{ $data->federal_state }}<br>
                                                @if ($data->po_box)
                                                    Postfach: {{ $data->po_box ?? '' }}<br>
                                                @endif

                                                <a href="mailto:{{ $data->email }}" target="_blank">{{ $data->email }}</a>

                                                <hr>
                                                <p>Gekauftes Produkt:<br> 
                                                    @if(isset($data->product->name))
                                                        {{ $data->product->name }}
                                                    @else
                                                        Produktname nicht vorhanden
                                                    @endif
                                                    <br><br>
                                                        Kategorie:<br> 
                                                    @if(isset($data->product->category->name))
                                                        {{ $data->product->category->name }}
                                                    @else
                                                        Kategorie nicht vorhanden
                                                    @endif
                                                    <br> 
                                                </p>

                                            </td>


                                            <td class="middle">
                                               <!-- <span class="label label-info">{{ $data->payouts_status ? 'Bezahlt' : 'Nicht bezahlt' }}</span> -->
                                               {{ $data->id }}
                                            </td>
                                            <td  class="middle">
                                            @if(!empty($data->shipping_method))
                                                <p class="text-success">Versandmethode:<br> {{ $data->shipping_method }}</p>
                                            @else
                                                <p>Keine Versandmethode festgelegt</p>
                                            @endif

                                            <hr>
                                            @if(!empty($data->tracking_Id))
                                                <p class="text-success"> Tracking ID:<br> {{ $data->tracking_Id }}</p>
                                            @else
                                                <p>Keine Tracking ID vorhanden</p>
                                            @endif
                                            <hr>
                                            @if(!empty($data->shipping_date))
                                            <p class="text-success">Versanddatum:<br>
                                                @if ($data->shipping_date)
                                                {{ Carbon\Carbon::parse($data->shipping_date)->format('d. m. Y') }}
                                                @endif
                                            </p>
                                             @else
                                                <p>Kein Versanddatum eingegeben</p>
                                            @endif
                                            <hr>

                                                @if (Storage::exists($data->video) && $data->status !== 3)
                                                    <p class="text-success">Es wurde ein Video versendet</p>
                                                    <hr>
                                                @endif
                                                @if (!$data->orderimages->isEmpty() && $data->status !== 3)
                                                    <p class="text-success">Es wurde ein Foto versendet</p>
                                                    <hr>
                                                @endif


                                                @if($data->shipping_method=='DHL')
                                                <a class="btn btn-sm btn-dark" target="_blank" href="https://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc={{$data->tracking_Id}}"><i class="voyager-eye"></i> <span
                                                        class="hidden-xs hidden-sm">Ansehen</span> </a>
                                                @endif
                                                @if($data->shipping_method=='Hermes')
                                                <a class="btn btn-sm btn-dark" target="_blank" href="https://www.myhermes.de/empfangen/sendungsverfolgung/suchen/sendungsinformation/{{$data->tracking_Id}}"><i class="voyager-eye"></i> <span
                                                        class="hidden-xs hidden-sm">Ansehen</span> </a>
                                                @endif
                                                @if($data->shipping_method=='DPD')
                                                <a class="btn btn-sm btn-dark" target="_blank" href="https://tracking.dpd.de/parcelstatus?query={{$data->tracking_Id}}&locale=de_DE"><i class="voyager-eye"></i> <span
                                                        class="hidden-xs hidden-sm">Ansehen</span> </a>
                                                @endif

                                                @if($data->shipping_method=='UPS')
                                                <a class="btn btn-sm btn-dark" target="_blank" href="http://wwwapps.ups.com/WebTracking/processInputRequest?sort_by=status&tracknums_displayed=1&TypeOfInquiryNumber=T&loc=de_DE&InquiryNumber1={{$data->tracking_Id}}&track.x=0&track.y=0"><i class="voyager-eye"></i> <span
                                                        class="hidden-xs hidden-sm">Ansehen</span> </a>
                                                @endif
                                                @if($data->shipping_method=='GLS')
                                                <a class="btn btn-sm btn-dark" target="_blank" href="https://www.gls-pakete.de/sendungsverfolgung?match={{$data->tracking_Id}}&txtAction=71000"><i class="voyager-eye"></i> <span
                                                        class="hidden-xs hidden-sm">Ansehen</span> </a>
                                                @endif
                                                @if($data->shipping_method=='Fedex')
                                                <a class="btn btn-sm btn-dark" target="_blank" href="https://www.fedex.com/fedextrack/?tracknumbers={{$data->tracking_Id}}&locale=de_DE&cntry_code=de"><i class="voyager-eye"></i> <span
                                                        class="hidden-xs hidden-sm">Ansehen</span> </a>
                                                @endif

                                                @if($data->shipping_method=='Deutsche Post')
                                                <p>Tracking ID:<br> {{$data->tracking_Id}}</p>
                                                <a class="btn btn-sm btn-dark" target="_blank" href="https://www.deutschepost.de/sendung/simpleQuery.html"><i class="voyager-eye"></i> <span
                                                        class="hidden-xs hidden-sm">Zur Seite der DP</span> </a>
                                                @endif

                                                @if($data->shipping_method=='Anderes')
                                                <p>Versendet mit: "Anderes", kein Tracking möglich</p>
                                                @endif

                                            </td>
                                            <td class="middle">
                                                @if(!empty($data->payment_gateway ))
                                                    <p>Zahlungsdienst:<br> <span class="text-uppercase">{{ $data->payment_gateway }}</span> </p>
                                                    <hr>
                                                @else
                                                @endif

                                                @if(!empty($data->payment_id ))
                                                    <p>Zahlungs-ID:<br> {{ $data->payment_id }} </p>
                                                @else
                                                @endif

                                            </td>
                                            <td  class="middle">
                                                {!! $data->message ? $data->message : "<p>Keine Sonderwünsche</p> "!!}
                                            </td>

                                            <!-- <td class="middle">
                                            @if($data->payouts_rerquest==1)
                                               <span class="label label-success">Ja</span>
                                               @else
                                               <span class="label label-danger">Nein</span>
                                               @endif

                                            </td> -->

                                            <td class="no-sort no-click bread-actions small">
                                                <a href="{{ route('voyager.orders.show', $data) }}" title="View"
                                                    class="btn btn-sm btn-warning pull-right view">
                                                    <i class="voyager-eye"></i> <span
                                                        class="hidden-xs hidden-sm">Ansehen</span>
                                                </a>

                                                @if($data->payouts_status==0)
                                                <a href="{{ route('payout', [$data, request()->page]) }}" 
                                                    onclick='return confirm("Bist du sicher, dass du die Bestellung als ausgezahlt markieren möchtest?");' 
                                                    title="Payouts"
                                                    class="btn btn-sm btn-success pull-right">
                                                     <i class="voyager-wallet"></i>
                                                     <span class="hidden-xs hidden-sm">Auszahlen</span>
                                                 </a>
                                                 
                                                @endif
                                                @if($data->status!==3)
                                            <a class="btn btn-sm btn-danger pull-right view" onclick="return confirm('Bist du sicher, dass du die Bestellung stornieren möchtest?');" href="{{route('admin.order.cancel',$data)}}" >Stornieren</a>
                                            @endif
                                           
                                          

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $dataTypeContent->withQueryString()->links() }}
                        </div>
                       
              
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop



