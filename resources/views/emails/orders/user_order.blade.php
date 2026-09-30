@php
    // Eine Bestätigung und eine Rechnung für die ganze Bestellung. $order ist der
    // Kopf der Bestellung; die Artikel hängen als Positionen daran, je einer pro
    // Herstellerin. Ältere Bestellungen haben keine Positionen – dann ist die
    // Bestellung selbst die einzige Position.
    $positions = $order->childrens->isNotEmpty() ? $order->childrens : collect([$order]);

    $anrede = \App\Order::firstFilled(
        $order->user->username,
        $order->user->name,
        $order->first_name,
    );

    $euro = fn ($value) => number_format((float) $value, 2, ',', '.') . ' €';

    // Herstellerinnen werden in der Mail nur mit Vornamen genannt.
    $vorname = fn ($vendor) => \App\Order::firstFilled($vendor?->first_name, $vendor?->name) ?? '–';
@endphp

@component('mail::message')
<h1 class="title">Bestellung {{ $order->orderNumber() }}</h1>
<div class="body-section">
Herzlichen Glückwunsch{{ $anrede ? ' ' . $anrede : '' }} zum Kauf.
<br><br>

Deine Bestellung <strong>{{ $order->orderNumber() }}</strong> umfasst
{{ $positions->count() === 1 ? 'einen Artikel' : $positions->count() . ' Artikel' }}:
<br><br>

<table>
    <thead>
        <tr>
            <th align="left">Artikel</th>
            <th align="left">Herstellerin</th>
            <th align="right">Betrag</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($positions as $position)
            <tr>
                <td align="left">{{ $position->product_name ?? $position->product->name }}</td>
                <td align="left">{{ $vorname($position->vendor) }}</td>
                <td align="right">{{ $euro($position->total) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@if (filled($order->discount_code) && (float) $order->discount > 0)
Zwischensumme: {{ $euro($positions->sum('total')) }}<br>
Gutschein {{ $order->discount_code }}: −{{ $euro($order->discount) }}<br>
@endif
<strong>Gesamtbetrag: {{ $euro($order->total) }}</strong>
<br><br>

@if ($positions->count() > 1)
{{-- Eine Rechnung für die ganze Bestellung. Die Artikel kommen von
     unterschiedlichen Herstellerinnen und werden einzeln versendet – das ist der
     einzige Grund, warum mehrere Pakete kommen können. --}}
Du bekommst eine Rechnung für die ganze Bestellung. Die Artikel werden von
unterschiedlichen Herstellerinnen einzeln versendet, es können also mehrere
Pakete kommen.
<br><br>
@endif

@component('mail::button', ['url' => route('invoice', $order->id), 'color' => 'green'])Rechnung ansehen
@endcomponent

Bitte logge dich in dein Dashboard ein, um weitere Informationen zu erhalten.<br><br>
Bei Fragen oder Problemen kontaktiere den Support unter:<br>
<a href="tel:03096607799">030 966 077 99</a>.
<br><br>
Ich wünsche weiterhin viel Freude auf <a href="https://fraukruner.de">fraukruner.de</a>.<br><br>
Liebe Grüße<br>
{{ config('app.name') }}
</div>
@endcomponent
