@component('mail::message')
<h1 class="title">Bestellung #{{ $order->id }}</h1>
<div class="body-section">
Herzlichen Glückwunsch {{ $order->user->username ?? $order->user->name}} zum Kauf.
<br><br>
Bitte logge dich in dein Dashboard ein, um weitere Informationen zu erhalten.<br><br>
Bei Fragen oder Problemen kontaktiere den Support unter:<br>
<a href="tel:03096607799">030 966 077 99</a>.
<br>
@php $url = route('invoice',$order->id); @endphp
@component('mail::button', ['url' => $url, 'color' => 'green'])Details anzeigen
@endcomponent
<br>
Ich wünsche weiterhin viel Freude auf <a href="https://fraukruner.de">fraukruner.de</a>.<br><br>
Liebe Grüße<br>
{{ config('app.name') }}
</div>
@endcomponent
