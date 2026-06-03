@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing') . ' ' .'Auszahlungen')

@section('page_header')

<h1 class="page-title" style="display: flex;align-items:center">
            <svg height="30" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
              </svg>
                   Push-Übersicht
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
                                            Was wurde gepusht
                                        </th>
                                        <th>
                                            Push-Option
                                        </th>
                                        <th>
                                            Preis
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Tax
                                        </th>
                                        <th>
                                            Start
                                        </th>
                                        <th>
                                            Ende
                                        </th>
                                        <th>
                                            Nutzer
                                        </th>
                                        <th>
                                            Aktionen
                                        </th>
                                       

                                        <th class="actions text-right dt-not-orderable"></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($boosts as $boost)
                                    <tr>

                                        <td>{{$boost->boostable?->name}} ({{ class_basename($boost->boostable_type) =='User' ?'Seller' :'Product' }})</td>
                                        <td>{{$boost->package->name}}</td>
                                        <td>{{Shop::price($boost->price)}}</td>
                                        <td>
                                            @if($boost->status == 0)
                                                <span class="label label-primary">Inaktiv</span>
                                            @else
                                                <span class="label label-info">Aktiv</span>
                                            @endif
                                        </td>
                                        <td>{{Shop::price($boost->tax)}}</td>
                                        <td>{{$boost->start_day->format('d.m.Y')}}</td>
                                        <td>{{$boost->end_day->format('d.m.Y')}}</td>
                                        <td>{{$boost->user?->name}} {{$boost->user?->last_name}}</td>
                                        <td>
                                            <a class="btn btn-primary" href="{{route('admin.boost.invoice',$boost)}}">Rechnung</a>
                                        </td>
                                    </tr>
                              
                                     
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $boosts->withQueryString()->links() }}
                        </div>
                       
              
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop



