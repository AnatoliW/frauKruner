@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing') . ' ' . 'Bestellungen(Vorkasse)')

@section('page_header')

    <div class="container-fluid">
        <h1 class="page-title" style="display: flex;align-items:center">
            <i class="voyager-dollar"></i>
            Bestellungen(Vorkasse)
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

                                        <input type="text" class="form-control" value="{{ request()->search }}"
                                            name="search" placeholder="Suchen">
                                    </div>
                                    <div class="col-md-1">
                                        <button class="btn btn-primary" type="submit">Suchen</button>
                                    </div>
                                </div>

                            </form>
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                    <tr>

                                        {{-- <th>
                                            ID
                                        </th> --}}
                                        <th>
                                            Haupt ID
                                        </th>
                                        <th>
                                            Nutzer ID
                                        </th>
                                        <th>
                                            Käufer
                                        </th>


                                        <th>
                                            Verkäuferin
                                        </th>
                                        <th>
                                            Produkt
                                        </th>
                                        <th>
                                            Gesamt
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
                                            {{-- <td class="middle">
                                                {{ $data->id }}
                                            </td> --}}
                                            <td class="middle">
                                                {{ $data->parent_id }}
                                            </td>

                                            <td class="middle">
                                                {{ $data->user_id }}
                                            </td>


                                            <td class="middle">
                                                {{ $data->first_name }} {{ $data->last_name }}<br>
                                                <a href="mailto:{{ $data->email }}" target="_blank">{{ $data->email }}</a>
                                            </td>


                                            <td>
                                                {{ $data->vendor->user_name }}
                                            </td>
                                            <td>
                                                @if ($data->product && $data->product->slug)
                                                    <a href="{{ route('product', $data->product->slug) }}" target="_blank">
                                                        {{ $data->product->name }} </a>
                                                @endif
                                            </td>
                                            <td class="middle">
                                                {{ $data->total }}
                                            </td>

                                            <td>
                                                {{ \Carbon\Carbon::parse($data->created_at)->format('d.m.Y') }}
                                            </td>
                                            <td class="no-sort no-click bread-actions text-right">
                                                {{-- @foreach ($actions as $action)
                                                    @if (!method_exists($action, 'massAction'))
                                                        @include('voyager::bread.partials.actions', [
                                                            'action' => $action,
                                                        ])
                                                    @endif
                                                @endforeach --}}
                                                {{-- @if ($data->status !== 3)
                                                    <a class="btn btn-sm btn-danger pull-right view"
                                                        onclick="return confirm('Bist du sicher, dass du die Bestellung stornieren möchtest?');"
                                                        href="{{ route('admin.order.cancel', $data->id) }}">Stornieren</a>
                                                @endif --}}
                                                @if ($data->status == 3)
                                                    <a class="btn btn-sm btn-dark pull-right view"
                                                        href="">Storniert</a>
                                                @endif
                                                {{-- @if ($data->payment_status == 0 && $data->payment_prove)
                                                    <a class="btn btn-sm btn-dark pull-right view"
                                                        href="{{ route('admin.order.payment.check', $data) }}">Check</a>
                                                @endif --}}
                                                <form action="{{ route('admin.order.payment.check.update', $data) }}"
                                                    method="post">
                                                    @csrf

                                                    <button type="submit" class="btn  btn-success"
                                                        style="margin-left: 10px">Bezahlt</button>
                                                </form>
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
