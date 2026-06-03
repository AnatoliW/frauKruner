@php
    $price = $order->total;
    $date = $order->created_at;
    $orderId = $order->id;
    $year = $order->created_at->year;
@endphp
@component('mail::message')
<h1 class="title">Bestellung #{{ $order->id }}</h1>
<div class="body-section">
Hallo Frau Kruner, <br>
ein Produkt wurde verkauft. <br />
Für weitere Details logge dich bitte ein:<br>

@php $url = route('voyager.orders.show',$order->id); @endphp
@component('mail::button', ['url' => $url, 'color' => 'green'])Details anzeigen
@endcomponent

<br>
Ich wünsche weiterhin viel Freude auf <a href="https://fraukruner.de">fraukruner.de</a>.<br><br>
Liebe Grüße<br>
{{ config('app.name') }}
</div>
@endcomponent
