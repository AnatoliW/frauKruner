@component('mail::message')
<h1 class="title">Bestellung #{{ $order->id }}</h1>
<div class="body-section">
Herzlichen Glückwunsch {{ $order->vendor->username ?? $order->vendor->name}},
dein Artikel wurde verkauft.<br><br>
Bitte logge dich in dein Dashboard ein, um weitere Informationen zu erhalten.<br><br>
Bei Fragen oder Problemen kontaktiere den Support unter:<br>
<a href="tel:03096607799">030 966 077 99</a>.
<br>
@php $url = route('voyager.orders.show',$order->id); @endphp
@component('mail::button', ['url' => $url, 'color' => 'green'])Details anzeigen
@endcomponent
<br>
Vielen Dank für Dein Vertrauen in mich.<br><br>
Liebe Grüße<br>
{{ config('app.name') }}
</div>
@endcomponent
