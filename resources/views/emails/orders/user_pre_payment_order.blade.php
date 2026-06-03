@php
    $price = $order->total;
    $date = $order->created_at;
    $orderId = $order->id;
    $year = $order->created_at->year;
@endphp

@component('mail::message')
<h1 class="title">Bestellung #{{ $order->id }}</h1>
<div class="body-section">
<p>Herzlichen Glückwunsch {{ $order->user->username ?? $order->user->name }} zum Kauf.</p>

<p>
    Bitte überweise: <strong>{{ $order->total }} €</strong><br>
    auf das folgende Konto unter Angabe des Verwendungszwecks:
</p>

<table>
    <tr>
        <td>Kontoinhaberin:</td>
        <td>Kathleen Krüger</td>
    </tr>
    <tr>
        <td>IBAN:</td>
        <td>DE65 1101 0101 5821 8303 92</td>
    </tr>
    <!-- <tr>
        <td>BIC:</td>
        <td>SOBKDEB2XXX</td>
    </tr> -->
    <tr>
        <td>
            Verwendungszweck:<br>
            <span>(bitte angeben)</span>
        </td>
        <td>FK{{ $order->created_at->year }}-{{ $order->id }}</td>
    </tr>
    <tr>
        <td>Betrag:</td>
        <td>{{ $order->total }} €</td>
    </tr>
</table><br><br>

<p>
Bitte zahle so schnell wie möglich, da dein gewünschtes Produkt nicht reserviert ist. Sollte dies nach deiner Überweisung und vor dem Zahlungseingang anderweitig verkauft werden,
erstatten wir dir dein Geld zurück.<br>
Bitte logge dich in dein Dashboard ein, um weitere Informationen zu erhalten.
</p>

<p>
    Bei Fragen oder Problemen kontaktiere den Support unter:<br>
    <a href="tel:03096607799">030 966 077 99</a>.
</p>

@php $url = route('buyer.orders'); @endphp
@component('mail::button', ['url' => $url, 'color' => 'green'])
Details anzeigen
@endcomponent

<p>
    Ich wünsche weiterhin viel Freude auf <a href="https://fraukruner.de">fraukruner.de</a>.
</p>

<p>
    Liebe Grüße<br>
    {{ config('app.name') }}
</p>
</div>
@endcomponent
