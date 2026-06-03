@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing') . ' ' .'Bestellungen')

@section('page_header')

    <div class="container-fluid">
        <h1 class="page-title" style="display: flex;align-items:center">
            <i class="voyager-dollar"></i>
              Bestellungen
    </div>
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
                                            ID
                                        </th>
                                        <th>
                                            Haupt ID
                                        </th>
                                        <th>
                                            Nutzer ID	
                                        </th>
                                        <th>
                                            Vorname
                                        </th>
                                        <th>
                                            Nachname
                                        </th>
                                        <th>
                                            Gesamt
                                        </th>

                                        <th>
                                            Verkäuferin
                                        </th>
                                        <th>
                                            Status der Auszahlung	
                                        </th>
                                        <th>
                                            Verkäuferin bekommt		
                                        </th>
                                        <th>
                                            Komission	
                                        </th>
                                        {{-- 
                                        <th>
                                            Versandmethode
                                        </th>
                                        --}}
                                        <th>
                                            Versanddatum	
                                        </th>
                                        
                                        <th>
                                            Zahlungs-ID		
                                        </th>
                                        
                                        <th>
                                            Bestelldatum	
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
                                                {{$data->id}}
                                            </td>
                                            <td>
                                                {{$data->parent_id}}
                                            </td>

                                            <td  class="middle">
                                                {{$data->user_id}}
                                            </td>


                                            <td class="middle">
                                             {{$data->first_name}}
                                            </td>
                                            <td  class="middle">
                                              {{$data->last_name}}
                                            </td>
                                            <td class="middle">
                                                {{$data->total}}
                                            </td>

                                            
                                            <td>
                                                {{$data->vendor->user_name}}
                                            </td>
                                        
                                            <td>
                                                @if($data->payouts_status)
                                                   <span class="label label-info">Ausgezahlt</span>
                                                @else
                                                <span class="label label-primary">Nicht ausgezahlt</span>

                                                @endif
                                               
                                            </td>
                                            <td>
                                                {{$data->vendor_total}}
                                            </td>
                                            <td>
                                                {{$data->commission}}
                                            </td>

                                            {{--
                                            <td  class="middle">
                                                {{$data->shipping_method}}
                                            </td>
                                            --}}
                                            <td>
                                            @if(!empty($data->shipping_date))
                                                @if ($data->shipping_date)
                                                {{ Carbon\Carbon::parse($data->shipping_date)->format('d. m. Y') }}
                                                @endif
                                             @else
                                                <p>Kein Versanddatum eingegeben</p>
                                            @endif

                                            </td>
                                           
                                            <td>
                                                {{$data->payment_id}}
                                            </td>
                                            
                                            <td>
                                            {{ \Carbon\Carbon::parse($data->created_at)->format('d.m.Y') }}
                                            </td>
                                            <td class="no-sort no-click bread-actions">
                                                @foreach($actions as $action)
                                                    @if (!method_exists($action, 'massAction'))
                                                        @include('voyager::bread.partials.actions', ['action' => $action])
                                                    @endif
                                                @endforeach
                                                @if($data->status!==3)
                                                <a class="btn btn-sm btn-danger pull-right view" onclick="return confirm('Bist du sicher, dass du die Bestellung stornieren möchtest?');" href="{{route('admin.order.cancel',$data->id)}}" >Stornieren</a>
                                                @endif
                                                @if($data->status==3)
                                                <a class="btn btn-sm btn-dark pull-right view"  href="" >Storniert</a>
                                                @endif
                                                @if($data->payment_status == 0 && $data->payment_prove )
                                              
                                                <a class="btn btn-sm btn-dark pull-right view"  href="{{route('admin.order.payment.check',$data)}}" >Check</a>
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



