@php
    // Eine Bestätigung und eine Rechnung für die ganze Bestellung. $order ist der
    // Kopf der Bestellung. Die Mail nennt nur Nummer und Gesamtbetrag, die
    // Positionen stehen in der Rechnung. Gezählt werden sie nur für den Hinweis
    // auf mehrere Pakete; ältere Bestellungen haben keine Positionen.
    $positions = $order->childrens->isNotEmpty() ? $order->childrens : collect([$order]);

    $anrede = \App\Order::firstFilled(
        $order->user->username,
        $order->user->name,
        $order->first_name,
    );

    $euro = fn ($value) => number_format((float) $value, 2, ',', '.') . ' €';
@endphp

@component('mail::message')
<div class="body-section">
Herzlichen Glückwunsch{{ $anrede ? ', ' . $anrede . ',' : '' }} zum Kauf.
<br><br>

Deine Bestellung <strong>{{ $order->orderNumber() }}</strong> ist bestätigt.
<br><br>

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
