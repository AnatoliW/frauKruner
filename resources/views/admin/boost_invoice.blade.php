@extends('voyager::master')

@section('page_title', 'Bestellung')

@section('page_header')

    <h1 class="page-title hidden-print">
       

{{-- 
            <a href="{{ route('admin.boosts.list') }}" class="btn btn-info">
                <span class="glyphicon glyphicon-pencil"></span>&nbsp;
                Bearbeiten
            </a> --}}
  
        <a href="{{ route('admin.boosts.list') }}" class="btn btn-warning">
            <span class="glyphicon glyphicon-list"></span>&nbsp;
            Alle Bestellungen
        </a>
        <button onclick="printDiv('buyerPrintWhole')" class="btn btn-dark">
            <span class="glyphicon glyphicon-list"></span>&nbsp;
            Drucken
        </button>
    </h1>
    @include('voyager::multilingual.language-selector')
@stop
@section('content')
    <style type="text/css">
        table.dataTable tbody td,
        table.dataTable tbody th {
            padding: 12px 19px;
        }

        .border {
            border: 1px solid #eee;
        }

        .p-2 {
            padding: 15px;
        }
    </style>
    <style>
        @page {
            size: auto;
            margin: 0mm;
        }
    </style>
    <style>
        .card {
            padding-bottom: 5px;
        }

        .table th {
            width: 15px;
        }
    </style>


    <div class="page-content read container-fluid" id="buyerPrintWhole">
        <h3>Admin Infos</h3>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                {{-- @if ($order->status == 3)
                                <h3 style="color:red">RECHNUNG WURDE STORNIERT!</h3>
                            @endif --}}

                                <p>
                                    <b>Käufer</b>
                       
                                    <br>
                                    {{ @$dataTypeContent->user_info->f_name ??  @$dataTypeContent->user->name  }} {{ @$dataTypeContent->user_info->l_name ??  @$dataTypeContent->user->last_name}}<br>
                                    {{ @$dataTypeContent->user_info->street ??  @$dataTypeContent->user->street }}
                                    {{ @$dataTypeContent->user_info->house_no ?? @$dataTypeContent->user->house_no }}<br>
                                    {{ @$dataTypeContent->user_info->zip ??  @$dataTypeContent->user->zip }}
                                    {{ @$dataTypeContent->user_info->federal_state ?? @$dataTypeContent->user->federal_state  }}<br>
                                    {{ @$dataTypeContent->user_info->email ??  @$dataTypeContent->user->email }}
                                </p>

                                @if (!is_null(@$dataTypeContent->user_info->vat_number) && @$dataTypeContent->user_info->vat_number !== '')
                                <p>Steuernummer: {{ @$dataTypeContent->user_info->vat_number }} </p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p><b>Anbieterinformation</b></p>
                                <p>
                                    Frau Kruner<br>
                                    Inh. Frau Kathleen Krüger<br>
                                    Schönhauser Allee 163<br>
                                    10435 Berlin</p>
                                <p>USt.-Ident.-Nr.: DE419009695</p>
                            </div>
                            <div class="col-md-6">
                            </div>



                            <div class=" col-md-6">
                                @php
                                    $cutoffDate = \Carbon\Carbon::parse(config('app.invoice_format_cutoff_date'));
                                    $payment = $dataTypeContent->payment ?? $dataTypeContent->payments->first();
                                    $useOldFormat = $dataTypeContent->created_at->lt($cutoffDate) && $payment && $payment->payment_trnx_id;
                                @endphp
                                <p>Rechnungs-Nr:
                                    @if ($useOldFormat)
                                        FKB{{ $payment->payment_trnx_id }}<br>
                                    @else
                                        PFK-{{ $dataTypeContent->created_at->format('Y') }}-{{ $dataTypeContent->id }}<br>
                                    @endif
                                    Rechnungs-Datum:
                                    {{ $dataTypeContent->created_at->format('d.m.Y') }} <br><br>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h3 class="panel-title">Details</h3>



                        <table class="table table-hover no-footer">
                            @php
                                $tax = $dataTypeContent->payments->first()->tax;

                            @endphp
                            <thead>
                                <tr role="row">
                                    <th class="sorting" colspan="1" rowspan="1" style="width: 15px;" tabindex="0">
                                        Produktname
                                    </th>
                                    <th>
                                        Basispreis
                                    </th>
                                    @if ($tax)
                                        <th>
                                            MwSt
                                        </th>
                                    @endif
                                    <th class="sorting" colspan="1" rowspan="1" style="width: 15px;" tabindex="0">
                                        Gesamt
                                    </th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr class="even" role="row">
                                    <td>
                                        <div> {{ $dataTypeContent->boostable->name ?? null }}</div>
                                    </td>

                                    <td>
                                        <div>{{ Shop::price($dataTypeContent->base_price) }}</div>
                                    </td>
                                    @if ($tax)
                                        <th>
                                            {{ Shop::price($tax) }}
                                        </th>
                                    @endif
                                    <td>
                                        <div>{{ Shop::price($dataTypeContent->price) }}</div>
                                    </td>

                                </tr>

                            </tbody>
                        </table>

                        @if (!$tax)
                            <div class="col-12 mt-5">
                                Gemäß § 19 UStG enthält der o.g. Rechnungsbetrag keine Umsatzsteuer.
                            </div>
                        @endif
                    </div>
                </div>

         
            </div>
        </div>
    </div>

    @section('javascript')


    <script type="text/javascript">
        function printDiv(divName) {
            var printContents = document.getElementById(divName).innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;
        }
    </script>
@stop


@endsection
