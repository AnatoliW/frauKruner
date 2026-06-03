@php
    $price = $order->total;
    $date = $order->created_at;
    $orderId = $order->id;
    $year = $order->created_at->year;
@endphp

@extends('layouts.app')
@section('title','Vielen Dank!')
@section('content')

<div class="container my-5">
<div class="row">
    <div class="col-12 text-center">
            <div class="thanks">
				<h2 class="mt-3 text-primary mb-5">Vielen Dank<br> für deinen Einkauf!</h2>
			</div>
    </div>
    <div class="col-12 d-flex flex-column align-items-center">
        <p class="text-center">
            Bitte überweise: <b class="h6">{{$order->total}} €</b><br>
            auf das folgende Konto unter Angabe des Verwendungszwecks:<br>
            </p>
            <table class="table text-secondary" style="max-width:600px;">
            <tbody>
                <tr>
                    <td scope="row">Kontoinhaberin:</td>
                    <th>Kathleen Krüger</th>
                </tr>
                <tr>
                    <td scope="row">IBAN:</td>
                    <th>DE65 1101 0101 5821 8303 92</th>
                </tr>
                <!-- <tr>
                    <td scope="row">BIC:</td>
                    <th>SOBKDEB2XXX</th>
                </tr> -->
                <tr>
                    <td scope="row">Verwendungszweck:<br><small class="text-primary">(zwingend erforderlich)</small></td>
                    <th>FK{{$order->created_at->year}}-{{$order->id}}</th>
                </tr>
                <tr>
                    <td scope="row">Betrag:</td>
                    <th>{{$order->total}} €</th>
                </tr>

            </tbody>
            </table>
    <p class="text-center">
        *Bitte zahle so schnell wie möglich, da dein gewünschtes Produkt nicht reserviert ist.<br> Sollte dies nach deiner Überweisung und vor dem Zahlungseingang anderweitig verkauft werden,<br> 
    erstatten wir dir dein Geld zurück.
</p>
    </div>
</div>
</div>


@endsection
@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {

    });
</script>

@endsection
